<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Twilio\Rest\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UserRegisterController extends Controller
{
    private $twilio;

    public function __construct()
    {
        $this->twilio = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }

    /**
     * Send OTP to phone number
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^09[0-9]{9}$/',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $phone = $request->phone;
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(5);

        // Check if user exists, create if not
        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'name' => 'New User', // You might want to update this later
                'password' => Hash::make(Str::random(16)), // Temporary password
                'role' => 'customer',
                'is_verified' => false
            ]
        );

        // Update OTP and expiration
        $user->update([
            'otp' => $otp,
            'expires_at' => $expiresAt
        ]);

        // Debug logging
        Log::info('OTP sent successfully', [
            'phone' => $phone,
            'otp' => $otp,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s')
        ]);

        try {
            // Send OTP via Twilio
            $this->twilio->messages->create(
                '+95' . substr($phone, 1), // Myanmar country code + phone number
                [
                    'from' => config('services.twilio.from'),
                    'body' => "Your verification code is: $otp. This code will expire in 5 minutes."
                ]
            );

            return response()->json([
                'message' => 'OTP sent successfully',
                'phone' => $phone
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to send OTP',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify OTP
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^09[0-9]{9}$/',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('phone', $request->phone)
            ->where('otp', $request->otp)
            ->where('expires_at', '>', now())
            ->first();

        if (!$user) {
            // Debug logging
            Log::error('OTP verification failed', [
                'phone' => $request->phone,
                'otp' => $request->otp,
                'current_time' => now()->format('Y-m-d H:i:s')
            ]);
            
            return response()->json([
                'error' => 'Invalid or expired OTP'
            ], 422);
        }

        // Clear OTP after successful verification
        $user->update([
            'otp' => null,
            'expires_at' => null,
            'is_verified' => true
        ]);

        return response()->json([
            'message' => 'OTP verified successfully'
        ]);
    }

    /**
     * Register a new user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|regex:/^09[0-9]{9}$/',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,astrology,customer'
        ];
    
        $validator = Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        try {
            $user = User::where('phone', $request->phone)->first();
    
            if ($user) {
                // Update existing user
                $user->update([
                    'name' => $request->name,
                    'password' => Hash::make($request->password),
                    'role' => $request->role,
                    'is_verified' => true,
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'password' => Hash::make($request->password),
                    'role' => $request->role,
                    'is_verified' => true,
                ]);
            }
    
            $token = $user->createToken('auth_token')->plainTextToken;
    
            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'is_verified' => $user->is_verified
                ]
            ], 201);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    

    
}