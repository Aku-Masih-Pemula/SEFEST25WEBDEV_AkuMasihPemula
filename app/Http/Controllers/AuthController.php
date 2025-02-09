<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
  public function register(Request $request)
  {
    // Check if the user is already authenticated
    if ($request->user()) {
      return response()->json([
        'message' => 'User already authenticated. Please log out before registering again.',
      ], 400);
    }

    // Validate input fields
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|email|string|unique:users,email',
      'password' => 'required|string|min:6',
      'phone' => 'required|string|max:16|unique:users,phone',
    ]);

    // Create the user
    $user = User::create([
      'name' => $validated['name'],
      'email' => $validated['email'],
      'password' => Hash::make($validated['password']),
      'phone' => $validated['phone'],
      'role' => 'consumer'
    ]);

    // Generate the token
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
      'message' => 'User successfully registered.',
      'data' => [
        'user' => $user,
        'token' => $token,
      ],
    ], 201);
  }

  public function registerSeller(Request $request)
  {
    $request->validate([
      'store_name' => 'required|string|max:255'
    ]);

    $user = Auth::user();

    if ($user->role == 'seller') {
      return response()->json([
        'message' => 'User is already a seller'
      ], 400);
    }

    $user->update(['role' => 'seller']);

    $seller = Seller::create([
      'user_id' => $user->id,
      'store_name' => $request->store_name,
    ]);

    return response()->json([
      'message' => 'Seller Account created successfully',
      'data' => [
        'user' => $user,
        'seller_data' => $seller
      ]
    ]);
  }

  // Login Function
  public function login(Request $request)
  {
    // Check if the user is already authenticated
    if ($request->user()) {
      return response()->json([
        'message' => 'User already authenticated. Please log out before logging in again.',
      ], 400);
    }

    // Validate input fields
    $validated = $request->validate([
      'email' => 'required|string|email',
      'password' => 'required|string',
    ]);

    // Find the user by email
    $user = User::where('email', $validated['email'])->first();

    // Check credentials
    if (!$user || !Hash::check($validated['password'], $user->password)) {
      return response()->json([
        'message' => 'Invalid credentials. Please check your email and password.',
      ], 401);
    }

    $user->tokens()->delete();


    // Generate the token
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
      'message' => 'User successfully logged in.',
      'data' => [
        'user' => $user,
        'token' => $token,
      ],
    ], 200);
  }

  // Logout Function
  public function logout(Request $request)
  {
    // Check if there's a logged-in user
    if (!$request->user()) {
      return response()->json([
        'message' => 'No user is currently logged in.',
      ], 400);
    }

    // Revoke the current access token
    $request->user()->currentAccessToken()->delete();

    return response()->json([
      'message' => 'User successfully logged out.',
    ], 200);
  }

  // Validate Token Function
  public function validateToken(Request $request)
  {
    $user = $request->user();

    if ($user) {
      return response()->json([
        'message' => 'Token is valid.',
        'data' => $user,
      ], 200);
    }

    return response()->json([
      'message' => 'Invalid or expired token.',
    ], 401);
  }
}
