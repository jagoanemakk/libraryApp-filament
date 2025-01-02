<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiAuthController extends Controller
{
    public function auth(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            /** @var \App\Models\MyUserModel $user **/
            $user = Auth::user();
            $token['token'] =  $user->createToken('API Token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'token' => $token,
                'user' => $user,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Login failed. Please check your credentials.',
        ], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }
}
