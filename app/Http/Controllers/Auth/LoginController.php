<?php

namespace App\Http\Controllers\Auth;

use App\Traits\ApiResponse;
use App\Jobs\Auth\LoginUserJob;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;

class LoginController extends Controller
{
    use ApiResponse;

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!$token = Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'status_code' => 401,
                'message' => 'Invalid credentials.'
            ], 401);
        }

        $user = Auth::user();

        // No need to load subscription for login - both subscribed/unsubscribed users can login

        $payload = [
            'iss' => URL::secure('/'),
            'sub' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'office_id' => $user->office_id,
        ];

        $token = JWTAuth::claims($payload)->fromUser($user);
        $cookie = cookie('auth_token', $token, 10080, '/', null, true, true, false, 'Strict');

        LoginUserJob::dispatch($user->id, now()->toDateTimeString());

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Login successful',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200)->withCookie($cookie);
    }
}