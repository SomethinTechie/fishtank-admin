<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function requestOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_number' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find or create user with just the user_number
        $user = User::firstOrCreate(
            ['user_number' => $request->user_number],
            ['name' => null, 'email' => null, 'password' => null]
        );

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Delete any existing OTPs for this user
        Otp::where('user_id', $user->id)->delete();
        
        // Create new OTP record
        Otp::create([
            'user_id' => $user->id,
            'otp' => Hash::make($otp),
            'expires_at' => Carbon::now()->addMinutes(5)
        ]);

        //temporary user email
        $user->email = 'mahlatsephokwane001@gmail.com';
        $user->save();

        // Send OTP via email
        Mail::raw(
            "Your OTP for Fishtank Admin is: {$otp}. This code will expire in 5 minutes.", 
            function($message) use ($user) {
                $message->to($user->email)
                    ->subject('Fishtank Admin - OTP Verification');
            }
        );

        return response()->json([
            'message' => 'OTP has been sent to your email'
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_number' => 'required|string',
            'otp' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('user_number', $request->user_number)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $latestOtp = Otp::where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$latestOtp) {
            return response()->json([
                'message' => 'No OTP request found'
            ], 404);
        }

        if (!$latestOtp->isValid()) {
            return response()->json([
                'message' => 'OTP has expired'
            ], 400);
        }

        if (!Hash::check($request->otp, $latestOtp->otp)) {
            return response()->json([
                'message' => 'Invalid OTP'
            ], 400);
        }

        // Delete used OTP
        $latestOtp->delete();

        // Generate authentication token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'OTP verified successfully',
            'user' => $user,
            'token' => $token
        ]);
    }
}
