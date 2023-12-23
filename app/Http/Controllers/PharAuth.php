<?php

namespace App\Http\Controllers;

use App\Models\Pharmacist;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class PharAuth extends Controller
{
    public function signUp(Request $request)
    {
        $validator = Validator::make($request->all(), ['username' => 'string|unique:users', 'phone_number' => 'string|unique:users', 'password' => 'string']);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->messages()]);
        } else {
            $user = User::create(['username' => $request->username, 'phone_number' => $request->phone_number, 'password' => $request->password,]);
            $pharmacist = Pharmacist::create(['user_id' => $user->id]);

            return response()->json(['message' => 'Pharmacist registered successfully!', 201]);
        }
    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), ['username' => 'string|exists:users,username',]);
        if (!$validator->fails()) {
            $auth = Auth::attempt(['username' => $request->username, 'password' => $request->password]);
            if ($auth) {
                $user = Auth::user();
                $token = $user->createToken('loginToken')->plainTextToken;
                return response()->json(['message' => 'Login done successfully!', 'access_token' => $token,'user_id'=>$user->id], 200);
            } else {
                return response()->json(['message' => 'Incorrect password']);
            }
        } else {
            return response()->json(['message' => $validator->messages()]);
        }
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        $user->tokens()->delete();
        return response()->json(['message' => 'Logout successful'], 200);
    }
}
