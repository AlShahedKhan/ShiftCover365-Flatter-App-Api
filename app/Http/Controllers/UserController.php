<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    public function getUser()
    {
        $user = Auth::user();
        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'User retrieved successfully',
            'user' => $user
        ]);
    }


public function saveUserAndOffice(Request $request)
{
    Log::info('saveUserAndOffice called', [
        'request_data' => $request->all()
    ]);

    try {
        $userId = auth()->id();
        $user = User::findOrFail($userId);

        // Get office id from the current user
        $office = Office::findOrFail($user->office_id);

        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|max:255|unique:users,email,' . $userId,
            'present_address'  => 'nullable|string',
            'profile_image'    => 'nullable|image',
            // 'location'         => 'nullable|string',

            // Office validation
            'company_name'     => 'required|string|max:255',
            'branch_name'      => 'nullable|string|max:255',
            'experience'       => 'nullable|string|max:255',
            'employee_id'      => 'nullable|string|max:50',
            'smart_id_image'   => 'nullable|image',
            'has_smart_id'     => 'required|boolean',
        ]);

        DB::beginTransaction();

        // Handle profile image upload if exists
        if ($request->hasFile('profile_image')) {
            $profilePath = $request->file('profile_image')->store('profiles', 'public');
            $user->profile_image = $profilePath;
        }

        // Handle smart ID image upload if exists
        if ($request->hasFile('smart_id_image')) {
            $smartIdPath = $request->file('smart_id_image')->store('smart_ids', 'public');
            $office->smart_id_image = $smartIdPath;
        }

        // Update office fields
        $office->company_name = $validated['company_name'];
        $office->branch_name = $validated['branch_name'] ?? null;
        $office->experience = $validated['experience'] ?? null;
        $office->employee_id = $validated['employee_id'] ?? null;
        $office->has_smart_id = $validated['has_smart_id'];
        $office->save();

        // Update user fields
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->present_address = $validated['present_address'] ?? null;
        // $user->location = $validated['location'] ?? null;
        // office_id remains unchanged
        $user->save();

        DB::commit();

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'User and office updated successfully',
            'data' => array_merge(
                $user->toArray(),
                $office->toArray()
            )
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();

        Log::error('Error in saveUserAndOffice', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'message' => 'Failed to update user and office',
            'error'   => $e->getMessage()
        ], 500);
    }
}

    /**
     * Delete the authenticated user's account
     */
    public function deleteAccount(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'status_code' => 401,
                'message' => 'Unauthorized',
            ], 401);
        }
        // Delete all shifts belonging to the user to avoid foreign key constraint errors
        $user->shifts()->delete();
        $user->delete();
        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Account deleted successfully.'
        ], 200);
    }


}
