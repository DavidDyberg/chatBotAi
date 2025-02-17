<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request) {
        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|confirmed|min:10'
            ]);

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);
            
            return response()->json(['message' => 'New user registerd successfully'], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while registering the user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request) {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'message' => ['The provided credentials are incorrect'],
                ]);
            }

            $token = $user->createToken('accestoken');
            return response()->json(['accestoken' => $token->plainTextToken], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while logging in',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request) {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out'], 200);
    }
}
