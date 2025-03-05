<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['login', 'register']);
    }
    
    public function register(Request $request)
    {
        $request->validate(rules: [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create(attributes: [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make(value: $request->password),
        ]);

        $token = $user->createToken(name: 'api-token')->plainTextToken;

        return response()->json(data: ['token' => $token], status: 201);
    }

    public function login(Request $request)
    {
        $request->validate(rules: [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where(column: 'email', operator: $request->email)->first();

        if (!$user || !Hash::check(value: $request->password, hashedValue: $user->password)) {
            return response()->json(data: ['message' => 'Invalid credentials'], status: 401);
        }

        $token = $user->createToken(name: 'api-token')->plainTextToken;

        $user->makeHidden(['password']);

        return response()->json([
            'user' => $user,  // Returning the user data
            'token' => $token, // Returning the token
        ], 201);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user->tokens()->delete();

        return response()->json(data: ['message' => 'Logged out']);
    }
}

