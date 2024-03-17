<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Throwable;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'phone_number' => 'required|unique:users|numeric|min:10',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8',
                'role' => 'nullable|in:customer,driver',
            ]);

            $user = User::create([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'token' => $token,
            ], 201); // HTTP Created status code

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 400); // HTTP Bad Request status code
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed',
                'error_details' => env('APP_DEBUG') === true ? $e->getMessage() : null
            ], 500);
        }
    }
    public function login(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required',
                'password' => 'required',
            ]);

            $user = User::where('phone_number', $request->username)->orWhere('email', $request->username)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                ], 404); // HTTP Not Found status code
            } elseif (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Incorrect password',
                ], 401); // HTTP Unauthorized status code
            }


            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'User logged in successfully',
                'token' => $token,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 400); // HTTP Bad Request status code
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login failed',
                'error_details' => env('APP_DEBUG') === true ? $e->getMessage() : null
            ], 500);
        }
    }
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'User logged out successfully',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logout failed',
                'error_details' => env('APP_DEBUG') === true ? $e->getMessage() : null
            ], 500);
        }
    }
}
