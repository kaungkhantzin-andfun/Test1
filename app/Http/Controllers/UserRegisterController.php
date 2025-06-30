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
use App\Models\UserMoney;
use App\Models\MoneyHistory;
use App\Models\InMoney;
use App\Models\Type;
use Illuminate\Support\Facades\DB;

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


        $existingUser = User::where('phone', $phone)->first();
        if ($existingUser && $existingUser->is_verified) {
            return response()->json([
                'error' => 'Phone number is already registered and verified.'
            ], 409); // 409 Conflict
        }


        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(5);


        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'name' => 'New User',
                'password' => Hash::make(Str::random(16)),
                'role' => 'customer',
                'is_verified' => false
            ]
        );


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
            'phone' => 'sometimes|string|regex:/^09[0-9]{9}$/|unique:users,phone,' . $user->id,
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
                'name',
                'email',
                'phone',
                'dob',
                'address',
                'hour',
                'minute'
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
                    'profile' => $user->profile ? asset('storage/' . $user->profile) : null,
                    'image' => $user->image ? array_map(function ($path, $index) {
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

   
    public function moneyinput(Request $request)
    {

        $validated = $request->validate([
            'money' => 'required|numeric|min:1',
            'type_id' => 'required|exists:types,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $user = auth('sanctum')->user();


        $type = Type::findOrFail($validated['type_id']);


        $imageUrl = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = 'payment_' . time() . '_' . $user->id . '.' . $image->getClientOriginalExtension();


            $path = $image->storeAs('public/payments', $imageName);

            $imageUrl = 'payments/' . $imageName;
        }

        $inMoney = InMoney::create([
            'user_id' => $user->id,
            'money' => $validated['money'],
            'status' => 'pending',
            'type' => $type->name,
            'type_id' => $type->id,
            'image' => $imageUrl
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Money input request submitted successfully',
            'data' => $inMoney
        ], 200);
    }

   
    public function moneyinputlist(Request $request)
    {
        $perPage = $request->json('per_page', 10);
        $page = $request->json('page', 1);
        
        $transactions = InMoney::where('user_id', auth('sanctum')->id())
            ->orderBy('created_at', 'desc')
            ->paginate(
                perPage: $perPage,
                page: $page
            );

        $transactions->getCollection()->transform(function ($transaction) {
            return [
                'id' => $transaction->id,
                'money' => (float) $transaction->money,
                'status' => $transaction->status,
                'type' => $transaction->type,
                'type_id' => $transaction->type_id,
                'image_url' => $transaction->image ? asset('storage/' . $transaction->image) : null,
                'created_at' => $transaction->created_at->toDateTimeString(),
                'updated_at' => $transaction->updated_at->toDateTimeString()
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $transactions
        ]);
    }

    /**
     * Approve money request and update user's balance
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function moneyapprove(Request $request)
    {
        $request->validate([
            'in_money_id' => 'required|exists:in_money,id',
            'status' => 'required|in:approved,rejected'
        ]);

        // Start database transaction
        return DB::transaction(function () use ($request) {
            // Find the money request
            $inMoney = InMoney::lockForUpdate()->findOrFail($request->in_money_id);

            // Check if already processed
            if ($inMoney->status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'This request has already been processed.'
                ], 400);
            }

            // Update the status
            $inMoney->status = $request->status;
            $inMoney->save();

            // If approved, update user's balance
            if ($request->status === 'approved') {
                $userMoney = UserMoney::firstOrNew(['user_id' => $inMoney->user_id]);
                $userMoney->money = ($userMoney->money ?? 0) + $inMoney->money;
                $userMoney->save();
            }

            return response()->json([
                'status' => true,
                'message' => 'Money request ' . $request->status . ' successfully',
                'data' => [
                    'in_money_id' => $inMoney->id,
                    'status' => $inMoney->status,
                    'user_id' => $inMoney->user_id,
                    'amount' => $inMoney->money,
                    'new_balance' => $request->status === 'approved' ? $userMoney->money : null
                ]
            ]);
        });
    }

    /**
     * Get authenticated user's current balance
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user_money_list(Request $request)
    {
        $user = $request->user();
        
        // Get user's current balance
        $userMoney = UserMoney::where('user_id', $user->id)->first();
        
        return response()->json([
            'status' => true,
            'current_balance' => $userMoney ? (float)$userMoney->money : 0
        ]);
    }

    /**
     * Get all bank/payment types
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function banktyle()
    {
        $types = Type::where('status', 'active')
            ->get(['id', 'name', 'number', 'photo', 'status'])
            ->map(function($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'number' => $type->number,
                    'photo' => $type->photo ? asset('storage/' . $type->photo) : null,
                    'status' => $type->status
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $types
        ]);
    }
}
