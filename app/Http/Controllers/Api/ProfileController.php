<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Enums\TxnType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Return authenticated user profile with balance & summary
     */
    public function index(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => true,
            'data'   => $this->buildProfile($user),
        ]);
    }

    /**
     * Update profile fields
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'first_name'   => 'sometimes|string|max:255',
            'last_name'    => 'sometimes|string|max:255',
            'phone'        => 'sometimes|string|max:30',
            'country'      => 'sometimes|string|max:100',
            'city'         => 'sometimes|string|max:100',
            'address'      => 'sometimes|string|max:500',
            'zip_code'     => 'sometimes|string|max:20',
            'gender'       => 'sometimes|in:Male,Female,Others',
            'date_of_birth'=> 'sometimes|date',
            'avatar'       => 'sometimes|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $data = $request->only([
            'first_name', 'last_name', 'phone', 'country',
            'city', 'address', 'zip_code', 'gender', 'date_of_birth',
        ]);

        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return response()->json([
            'status'  => true,
            'message' => 'Profile updated successfully.',
            'data'    => $this->buildProfile($user->fresh()),
        ]);
    }

    /**
     * Return balance and transaction summary
     */
    public function balance(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => true,
            'data'   => [
                'balance'          => (float) $user->balance,
                'currency_symbol'  => setting('currency_symbol', 'global'),
                'total_deposit'    => (float) $user->totalDeposit(),
                'total_withdraw'   => (float) $user->totalWithdraw(),
                'total_transfer'   => (float) $user->totalTransfer(),
                'total_profit'     => (float) $user->totalProfit(),
            ],
        ]);
    }

    /**
     * Recent transactions (last 10)
     */
    public function recentTransactions(Request $request)
    {
        $user  = $request->user();
        $limit = (int) $request->get('limit', 10);

        $transactions = Transaction::where('user_id', $user->id)
            ->latest()
            ->take($limit)
            ->get()
            ->map(fn($t) => [
                'id'           => $t->id,
                'tnx'          => $t->tnx,
                'type'         => $t->type instanceof \BackedEnum ? $t->type->value : $t->type,
                'description'  => $t->description,
                'method'       => $t->method,
                'amount'       => (float) $t->amount,
                'charge'       => (float) $t->charge,
                'final_amount' => (float) $t->final_amount,
                'status'       => $t->status instanceof \BackedEnum ? $t->status->value : $t->status,
                'created_at'   => $t->attributes['created_at'] ?? (string) $t->created_at,
            ]);

        return response()->json([
            'status' => true,
            'data'   => $transactions,
        ]);
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------
    private function buildProfile($user): array
    {
        $avatar = null;
        if (!empty($user->avatar)) {
            $avatar = str_starts_with($user->avatar, 'http')
                ? $user->avatar
                : Storage::disk('public')->url(ltrim($user->avatar, '/'));
        }

        return [
            'id'              => $user->id,
            'first_name'      => $user->first_name,
            'last_name'       => $user->last_name,
            'full_name'       => $user->full_name,
            'email'           => $user->email,
            'username'        => $user->username,
            'phone'           => $user->phone,
            'country'         => $user->country,
            'city'            => $user->city,
            'address'         => $user->address,
            'zip_code'        => $user->zip_code,
            'gender'          => $user->gender,
            'date_of_birth'   => $user->date_of_birth,
            'avatar'          => $avatar,
            'account_number'  => $user->account_number,
            'balance'         => (float) $user->balance,
            'currency_symbol' => setting('currency_symbol', 'global'),
            'kyc'             => $user->kyc,
            'status'          => $user->status,
            'two_fa'          => $user->two_fa,
            'deposit_status'  => $user->deposit_status,
            'withdraw_status' => $user->withdraw_status,
            'transfer_status' => $user->transfer_status,
            'total_deposit'   => (float) $user->totalDeposit(),
            'total_withdraw'  => (float) $user->totalWithdraw(),
            'total_transfer'  => (float) $user->totalTransfer(),
            'total_profit'    => (float) $user->totalProfit(),
        ];
    }
}

