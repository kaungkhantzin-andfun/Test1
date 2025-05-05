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
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');
        
        Log::info('Twilio Configuration', [
            'sid_exists' => !empty($sid),
            'token_exists' => !empty($token),
            'from_exists' => !empty($from)
        ]);
        
        $this->twilio = new Client($sid, $token);
    }

    /**
     * Send OTP to phone number
     *
     * @param Request $request
     * @return JsonResponse
     */
    // public function sendOtp(Request $request): JsonResponse
    // {
    //     $validator = Validator::make($request->all(), [
    //         'phone' => 'required|string|regex:/^09[0-9]{9}$/',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 422);
    //     }

    //     $phone = $request->phone;
    //     $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    //     $expiresAt = now()->addMinutes(5);


    //     $user = User::firstOrCreate(
    //         ['phone' => $phone],
    //         [
    //             'name' => 'New User',
    //             'password' => Hash::make(Str::random(16)),
    //             'role' => 'customer',
    //             'is_verified' => false
    //         ]
    //     );


    //     $user->update([
    //         'otp' => $otp,
    //         'expires_at' => $expiresAt
    //     ]);


    //     Log::info('OTP sent successfully', [
    //         'phone' => $phone,
    //         'otp' => $otp,
    //         'expires_at' => $expiresAt->format('Y-m-d H:i:s')
    //     ]);

    //     try {

    //         $this->twilio->messages->create(
    //             '+95' . substr($phone, 1),
    //             [
    //                 'from' => config('services.twilio.from'),
    //                 'body' => "Your verification code is: $otp. This code will expire in 5 minutes."
    //             ]
    //         );

    //         return response()->json([
    //             'message' => 'OTP sent successfully',
    //             'phone' => $phone
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => 'Failed to send OTP',
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function sendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^09[0-9]{9}$/',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $phone = $request->phone;
    
        // ✅ Check if phone is already registered with verified account
        $existingUser = User::where('phone', $phone)->first();
        if ($existingUser && $existingUser->is_verified) {
            return response()->json([
                'error' => 'Phone number is already registered and verified.'
            ], 409); // 409 Conflict
        }
    
        // ✅ Generate OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(5);
    
        // ✅ Create or get existing user (unverified)
        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'name' => 'New User',
                'password' => Hash::make(Str::random(16)),
                'role' => 'customer',
                'is_verified' => false
            ]
        );
    
        // ✅ Update OTP
        $user->update([
            'otp' => $otp,
            'expires_at' => $expiresAt
        ]);
    
        Log::info('OTP sent successfully', [
            'phone' => $phone,
            'otp' => $otp,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s')
        ]);
    
        try {
            $this->twilio->messages->create(
                '+95' . substr($phone, 1),
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
            'role' => 'required|in:admin,astrology,customer',
            'dob' => 'required|date'
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
                    'dob' => $request->dob
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'password' => Hash::make($request->password),
                    'role' => $request->role,
                    'is_verified' => true,
                    'dob' => $request->dob
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
                    'is_verified' => $user->is_verified,
                    'dob' => $user->dob
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function useredit(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $rules = [
            'name' => 'sometimes|string|max:255',
          'email' => 'sometimes|nullable|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|regex:/^09[0-9]{9}$/|unique:users,phone,'.$user->id,
            'dob' => 'sometimes|date',
            'address' => 'sometimes|string',
            'hour' => 'sometimes|string',
            'minute' => 'sometimes|string',
            'profile' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image' => 'sometimes|array', 
            'image.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', 
            'password' => 'sometimes|string|min:8'
        ];
    
        $validator = Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            Log::info('Validation failed: ', $validator->errors()->toArray()); 
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        try {
            $updateData = $request->only([
                'name', 'email', 'phone', 'dob', 'address', 
                'hour', 'minute'
            ]);
    
           
            if ($request->hasFile('profile')) {
                $profilePath = $request->file('profile')->store('profiles', 'public');
                $updateData['profile'] = $profilePath;
            }
    
            
            if ($request->hasFile('image')) {
                $imageFiles = $request->file('image');
                $imagePaths = $user->image ?? [];
    
                
                $files = is_array($imageFiles) ? $imageFiles : [$imageFiles];
                
                foreach ($files as $image) {
                    if (count($imagePaths) >= 5) {
                        break;                  
                    }
                    $path = $image->store('user_images', 'public');
                    $imagePaths[] = $path;
                }
                
                $updateData['image'] = $imagePaths;
            }
    
            if ($request->has('password')) {
                $updateData['password'] = Hash::make($request->password);
            }
    
            $user->update($updateData);
    
            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'dob' => $user->dob,
                    'address' => $user->address,
                    'hour' => $user->hour,
                    'minute' => $user->minute,
                    'profile' => $user->profile ? asset('storage/'.$user->profile) : null,
                    'image' => $user->image ? array_map(function($path, $index) {
                        return [$index + 1 => asset('storage/' . $path)];
                    }, $user->image, array_keys($user->image)) : [],
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'is_verified' => $user->is_verified
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Update failed: ' . $e->getMessage());   
            return response()->json([
                'message' => 'Profile update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
