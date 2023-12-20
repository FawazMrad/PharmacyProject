<?php

namespace App\Http\Controllers;
use App\Models\Pharmacist;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class PharAuth extends Controller
{
    public function signUp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'string|unique:users',
            'phone_number' => 'string|unique:users',
            'password' => 'string'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()
            ]);
        }
 else {
            $user = User::create([
                'username' => $request->username,
                'phone_number' => $request->phone_number,
                'password' => $request->password,
            ]);
            $pharmacist = Pharmacist::create([
                'user_id' => $user->id
            ]);

            return response()->json([
                'message' => 'Pharmacist registered successfully',201
            ]);
        }
    }
}
