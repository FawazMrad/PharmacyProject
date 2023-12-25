<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminAuth extends Controller
{
    public function login(Request $request)
    {
        $adminName = $request->username;
        $adminPass = $request->password;
        if ($adminName === "Admin" && $adminPass === "admin") {
            $auth = Auth::attempt(['username' => $adminName, 'password' => $adminPass]);
            $user = Auth::user();
            $token = $user->createToken('loginToken')->plainTextToken;
            return response()->json(['message' => 'Login done successfully!', 'access_token' => $token,'user_id'=>$user->id], 200);
        } else {
            return response()->json(['message' => 'Login failed']);
        }
    }

    public function logout()
    {
        $user = Auth::user();
        $user->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully', 200]);
    }
}

