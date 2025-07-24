<?php

namespace App\Http\Controllers;

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
}
