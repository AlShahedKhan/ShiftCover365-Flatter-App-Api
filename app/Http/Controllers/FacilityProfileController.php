<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\AuthHelper;
use App\Helpers\ResponseHelper;

class FacilityProfileController extends Controller
{
    /**
     * Get facility profile for any authenticated user
     */
    public function getProfile()
    {
        AuthHelper::checkUser();
        $user = Auth::user();

        return ResponseHelper::success([
            'profile' => [
                'personal_details' => [
                    'name' => $user->name,
                    'user_id' => $user->id,
                    'designation' => $user->designation ?? '',
                ],
                'job_details' => [
                    'role' => $user->role,
                    'experience' => $user->experience ?? '',
                    'case_types' => $user->case_types ?? '',
                ],
                'administrative_info' => [
                    'joining_date' => $user->joining_date ?? '',
                    'work_location' => $user->work_location ?? $user->location ?? '',
                    'employment_type' => $user->employment_type ?? '',
                ]
            ]
        ], 'Facility profile retrieved successfully', 200);
    }

    /**
     * Update facility profile for any authenticated user
     */
    public function updateProfile(Request $request)
    {
        AuthHelper::checkUser();
        $user = Auth::user();

        $validated = $request->validate([
            'personal_details.name' => 'sometimes|string|max:255',
            'personal_details.designation' => 'sometimes|string|max:255',
            'job_details.experience' => 'sometimes|string|max:255',
            'job_details.case_types' => 'sometimes|string|max:500',
            'administrative_info.joining_date' => 'sometimes|date',
            'administrative_info.work_location' => 'sometimes|string|max:255',
            'administrative_info.employment_type' => 'sometimes|string|max:100',
        ]);

        // Update user fields based on validated data
        if (isset($validated['personal_details'])) {
            if (isset($validated['personal_details']['name'])) {
                $user->name = $validated['personal_details']['name'];
            }
            if (isset($validated['personal_details']['designation'])) {
                $user->designation = $validated['personal_details']['designation'];
            }
        }

        if (isset($validated['job_details'])) {
            if (isset($validated['job_details']['experience'])) {
                $user->experience = $validated['job_details']['experience'];
            }
            if (isset($validated['job_details']['case_types'])) {
                $user->case_types = $validated['job_details']['case_types'];
            }
        }

        if (isset($validated['administrative_info'])) {
            if (isset($validated['administrative_info']['joining_date'])) {
                $user->joining_date = $validated['administrative_info']['joining_date'];
            }
            if (isset($validated['administrative_info']['work_location'])) {
                $user->work_location = $validated['administrative_info']['work_location'];
            }
            if (isset($validated['administrative_info']['employment_type'])) {
                $user->employment_type = $validated['administrative_info']['employment_type'];
            }
        }

        $user->save();

        return ResponseHelper::success([
            'profile' => [
                'personal_details' => [
                    'name' => $user->name,
                    'user_id' => $user->id,
                    'designation' => $user->designation ?? '',
                ],
                'job_details' => [
                    'role' => $user->role,
                    'experience' => $user->experience ?? '',
                    'case_types' => $user->case_types ?? '',
                ],
                'administrative_info' => [
                    'joining_date' => $user->joining_date ?? '',
                    'work_location' => $user->work_location ?? $user->location ?? '',
                    'employment_type' => $user->employment_type ?? '',
                ]
            ]
        ], 'Facility profile updated successfully', 200);
    }
}