<?php

namespace App\Http\Controllers;

use App\Models\Consent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\AuthHelper;

class ConsentController extends Controller
{


    /**
     * Give consent agreement
     */
    public function giveConsent(Request $request)
    {
        AuthHelper::checkUser();

        $validated = $request->validate([
            'consent_given' => 'required'
        ]);

        // Convert string to boolean if needed
        $consentGiven = filter_var($request->consent_given, FILTER_VALIDATE_BOOLEAN);

        if (!$consentGiven) {
            return response()->json([
                'success' => false,
                'status_code' => 422,
                'message' => 'Consent must be given to proceed',
                'errors' => [
                    'consent_given' => ['Consent must be given to proceed']
                ]
            ], 422);
        }

        $user = Auth::user();

        // Create or update consent record
        $consent = Consent::updateOrCreate(
            ['user_id' => $user->id],
            ['consent_given' => true]
        );

        // Load user with relationships
        $user->load(['subscription.plan', 'office']);

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Consent given successfully',
            'user' => $user,
            'consent_status' => [
                'user_id' => $consent->user_id,
                'consent_given' => $consent->consent_given,
                'updated_at' => $consent->updated_at
            ]
        ], 200);
    }


    /**
     * Get user's consent status
     */
    public function getConsentStatus()
    {
        AuthHelper::checkUser();

        $user = Auth::user();
        $consent = $user->consent;

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Consent status retrieved successfully',
            'consent_status' => [
                'consent_given' => $consent ? $consent->consent_given : false,
                'user_info' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'office_id' => $user->office_id,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ],
                'signed_at' => $consent && $consent->consent_given ? $consent->updated_at : null
            ]
        ], 200);
    }
}
