<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        // Validate request input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to authenticate user
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = $request->user(); // Gets the authenticated user
        $token = $user->createToken('api-token')->plainTextToken; // Generates token

        // Regenerate session (important for security)
        $request->session()->regenerate();

        return response()->json([
            'user' =>  $user,
            'token' =>  $token,
            'message' => 'Login successful',
        ], 200);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        return response()->json(['message' => 'Logged out']);
    }
}

