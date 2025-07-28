<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class VerificationController extends Controller
{
    // Get verification status for the current user
    public function status(Request $request)
    {
        $user = Auth::user();
        Log::info('Verification status requested', ['user_id' => $user->id]);
        $response = [
            'steps' => [
                [ 'name' => 'Digital Accountability Agreement', 'completed' => (bool) $user->agreement_signed ],
                [ 'name' => 'Verified Staff Profile', 'completed' => (bool) $user->profile_verified ],
                [ 'name' => 'Internal Staff Code', 'completed' => (bool) $user->staff_code_verified ],
            ]
        ];
        Log::info('Verification status response', ['user_id' => $user->id, 'response' => $response]);
        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Verification status fetched successfully',
            'user' => $user,
            'steps' => $response['steps'],
            'access_token' => null,
            'token_type' => 'Bearer',
        ], 200);
    }

    // Save/update personal information
    public function saveProfile(Request $request)
    {
        $user = Auth::user();
        Log::info('Save profile requested', ['user_id' => $user->id, 'request' => $request->all()]);
        $data = $request->validate([
            'name' => 'string',
            'company' => 'string|nullable',
            'branch' => 'string|nullable',
            'experience' => 'string|nullable',
            'email' => 'email',
            'present_address' => 'string|nullable',
            'location' => 'string|nullable',
            'employee_id' => 'string|nullable',
            'has_smart_id' => 'boolean',
            'profile_image' => 'file|nullable',
            'id_document' => 'file|nullable',
        ]);
        $user->fill($data);
        if ($request->hasFile('profile_image')) {
            $user->profile_image = $request->file('profile_image')->store('profiles', 'public');
        }
        if ($request->hasFile('id_document')) {
            $user->id_document = $request->file('id_document')->store('ids', 'public');
        }
        $user->save();
        Log::info('Profile saved', ['user_id' => $user->id, 'user' => $user->toArray()]);
        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Profile updated successfully',
            'user' => $user,
            'access_token' => null,
            'token_type' => 'Bearer',
        ], 200);
    }

    // Get agreement text
    public function getAgreement()
    {
        $text = 'Accountable for their duties. Their profile may be shared with the branch manager for safety/security.';
        Log::info('Agreement text requested', ['agreement' => $text]);
        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Agreement text fetched successfully',
            'agreement' => $text,
            'user' => Auth::user(),
            'access_token' => null,
            'token_type' => 'Bearer',
        ], 200);
    }

    // Sign agreement
    public function signAgreement(Request $request)
    {
        $user = Auth::user();
        Log::info('Sign agreement requested', ['user_id' => $user->id, 'request' => $request->all()]);
        $request->validate([
            'signature' => 'required|file|image',
        ]);
        if ($request->hasFile('signature')) {
            $path = $request->file('signature')->store('signatures', 'public');
            $user->agreement_signed = true;
            $user->signature = $path;
            $user->save();
            Log::info('Agreement signed (image uploaded)', ['user_id' => $user->id, 'signature_path' => $path]);
            return response()->json([
                'success' => true,
                'status_code' => 200,
                'message' => 'Agreement signed successfully',
                'user' => $user,
                'access_token' => null,
                'token_type' => 'Bearer',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'status_code' => 422,
                'message' => 'Signature image is required',
                'user' => $user,
                'access_token' => null,
                'token_type' => 'Bearer',
            ], 422);
        }
    }

    // Validate staff code
    public function validateStaffCode(Request $request)
    {
        $user = Auth::user();
        Log::info('Validate staff code requested', ['user_id' => $user->id, 'request' => $request->all()]);
        $request->validate([
            'staff_code' => 'string|required|size:4',
        ]);
        // Example: compare with a hashed value (should be stored securely)
        $hashed = $user->staff_code_hash;
        $valid = Hash::check($request->input('staff_code'), $hashed);
        if ($valid) {
            $user->staff_code_verified = true;
            $user->save();
        }
        Log::info('Staff code validation result', ['user_id' => $user->id, 'valid' => $valid]);
        return response()->json([
            'success' => $valid,
            'status_code' => $valid ? 200 : 422,
            'message' => $valid ? 'Staff code validated successfully' : 'Invalid staff code',
            'user' => $user,
            'access_token' => null,
            'token_type' => 'Bearer',
        ], $valid ? 200 : 422);
    }
}
