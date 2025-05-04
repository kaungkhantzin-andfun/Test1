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

    $token = $user->createToken($request->device_name)->plainTextToken;

    $baseUrl = url('storage');

    // Decode image if stored as JSON string
    $imageArray = is_string($user->image) ? json_decode($user->image, true) : $user->image;

    $formattedImages = [];
    if (is_array($imageArray)) {
        foreach ($imageArray as $index => $imgPath) {
            $formattedImages[] = [
                (string)($index + 1) => "{$baseUrl}/{$imgPath}"
            ];
        }
    }

    return response()->json([
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'otp' => $user->otp,
            'address' => $user->address,
            'hour' => $user->hour,
            'minute' => $user->minute,
            'dob' => $user->dob,
            'image' => $formattedImages,
            'profile' => $user->profile ? "{$baseUrl}/{$user->profile}" : null,
            'expires_at' => $user->expires_at,
            'is_verified' => $user->is_verified,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ]
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