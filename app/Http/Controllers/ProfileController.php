<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function adminProfile(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'user' => $request->user(),
            'admin_specific_data' => 'Admin specific data here'
        ]);
    }

    public function astrologyProfile(Request $request)
    {
        if (!$request->user()->isAstrology()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'user' => $request->user(),
            'astrology_specific_data' => 'Astrology specific data here'
        ]);
    }

    public function customerProfile(Request $request)
{
    $user = $request->user();

    if (!$user->isCustomer()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $baseUrl = url('storage');

    // Handle image field
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
        ],
        'customer_specific_data' => 'Customer specific data here'
    ]);
}

}