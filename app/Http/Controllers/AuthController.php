<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {  
      
        Log::info('Login attempt started');
        Log::info('Request data:', $request->all());
        
        $user = User::where('phone', $request->phone)->first();
        Log::info('User found:', $user ? ['id' => $user->id, 'phone' => $user->phone] : 'No user found');

        if (!$user || !Hash::check($request->password, $user->password)) {
            Log::info('Login failed - invalid credentials');
            throw ValidationException::withMessages([
                'phone' => ['The provided credentials are incorrect.'],
            ]);
        }

        Log::info('Login successful');
        return response()->json([
            'token' => $user->createToken($request->device_name)->plainTextToken,
            'user' => $user,
            'role' => $user->role,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function profile(Request $request)
    {
        return response()->json($request->user());
    }
}