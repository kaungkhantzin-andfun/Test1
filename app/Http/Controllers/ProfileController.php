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
        if (!$request->user()->isCustomer()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'user' => $request->user(),
            'customer_specific_data' => 'Customer specific data here'
        ]);
    }
}