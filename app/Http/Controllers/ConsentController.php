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
            'consent_given' => 'required|boolean|accepted'
        ]);

        $user = Auth::user();

        // Create or update consent record
        Consent::updateOrCreate(
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
            'access_token' => null,
            'token_type' => 'Bearer'
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
