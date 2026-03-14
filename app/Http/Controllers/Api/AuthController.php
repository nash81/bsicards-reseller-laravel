<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginActivities;
use App\Models\User;
use App\Enums\TxnType;
use App\Enums\TxnStatus;
use App\Traits\NotifyTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Txn;

class AuthController extends Controller
{
    use NotifyTrait;

    /**
     * Login and return Sanctum token
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if ($user->status == 0) {
            return response()->json([
                'status'  => false,
                'message' => 'Your account has been suspended. Please contact support.',
            ], 403);
        }

        // Revoke old tokens (optional: single device)
        // $user->tokens()->delete();

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Login successful.',
            'token'   => $token,
            'user'    => $this->userProfile($user),
        ]);
    }

    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        if (! setting('account_creation', 'permission')) {
            return response()->json([
                'status'  => false,
                'message' => 'User registration is currently closed.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255|unique:users',
            'password'   => ['required', 'confirmed', Password::defaults()],
            'phone'      => 'nullable|string|max:20',
            'country'    => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'username'   => $request->first_name . $request->last_name . rand(1000, 9999),
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'phone'      => $request->phone ?? '',
            'country'    => $request->country ?? '',
            'portfolios' => json_encode([]),
        ]);

        // Signup bonus
        if (setting('referral_signup_bonus', 'permission') && (float) setting('signup_bonus', 'fee') > 0) {
            $signupBonus = (float) setting('signup_bonus', 'fee');
            $user->increment('balance', $signupBonus);
            Txn::new($signupBonus, 0, $signupBonus, 'system', 'Signup Bonus', TxnType::SignupBonus, TxnStatus::Success, null, null, $user->id);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Registration successful.',
            'token'   => $token,
            'user'    => $this->userProfile($user),
        ], 201);
    }

    /**
     * Logout – revoke current token
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Return authenticated user profile
     */
    public function me(Request $request)
    {
        return response()->json([
            'status' => true,
            'user'   => $this->userProfile($request->user()),
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json([
            'status'  => true,
            'message' => 'Password changed successfully.',
        ]);
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------
    private function userProfile(User $user): array
    {
        $avatar = null;
        if (!empty($user->avatar)) {
            $avatar = str_starts_with($user->avatar, 'http')
                ? $user->avatar
                : Storage::disk('public')->url(ltrim($user->avatar, '/'));
        }

        return [
            'id'           => $user->id,
            'first_name'   => $user->first_name,
            'last_name'    => $user->last_name,
            'full_name'    => $user->full_name,
            'email'        => $user->email,
            'username'     => $user->username,
            'phone'        => $user->phone,
            'country'      => $user->country,
            'city'         => $user->city,
            'address'      => $user->address,
            'zip_code'     => $user->zip_code,
            'gender'       => $user->gender,
            'date_of_birth'=> $user->date_of_birth,
            'avatar'       => $avatar ?: asset('assets/images/default.png'),
            'account_number' => $user->account_number,
            'balance'      => (float) $user->balance,
            'kyc'          => $user->kyc,
            'status'       => $user->status,
            'two_fa'       => $user->two_fa,
            'deposit_status'  => $user->deposit_status,
            'withdraw_status' => $user->withdraw_status,
            'transfer_status' => $user->transfer_status,
        ];
    }
}

