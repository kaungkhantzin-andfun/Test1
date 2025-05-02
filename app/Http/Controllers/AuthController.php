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
        $user = User::where('phone', $request->phone)->first();
    
        if (!$user) {
            return response()->json([
                'message' => 'No user found with this phone number'
            ], 401);
        }
    
        if (!Hash::check($request->password, $user->password)) {
           
            logger()->error('Password mismatch', [
                'input' => $request->password,
                'stored_hash' => $user->password,
                'rehashed_input' => Hash::make($request->password)
            ]);
            
            return response()->json([
                'message' => 'Incorrect password'
            ], 401);
        }
    
        return response()->json([
            'token' => $user->createToken($request->device_name)->plainTextToken,
            'user' => $user->makeHidden(['password', 'remember_token'])
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