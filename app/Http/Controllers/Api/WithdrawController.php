<?php

namespace App\Http\Controllers\Api;

use App\Enums\TxnStatus;
use App\Enums\TxnType;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\WithdrawAccount;
use App\Models\WithdrawalSchedule;
use App\Models\WithdrawMethod;
use App\Traits\ImageUpload;
use App\Traits\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Txn;

class WithdrawController extends Controller
{
    use ImageUpload, Payment;

    public function methods(): \Illuminate\Http\JsonResponse
    {
        $methods = WithdrawMethod::with('gateway')
            ->where('status', true)
            ->get()
            ->map(function (WithdrawMethod $method) {
                return [
                    'id' => $method->id,
                    'name' => $method->name,
                    'currency' => $method->currency,
                    'type' => $method->type,
                    'charge' => (float) $method->charge,
                    'charge_type' => $method->charge_type,
                    'min_withdraw' => (float) $method->min_withdraw,
                    'max_withdraw' => (float) $method->max_withdraw,
                    'rate' => (float) $method->rate,
                    'required_time' => (int) $method->required_time,
                    'required_time_format' => $method->required_time_format,
                    'icon' => $method->icon,
                    'fields' => $method->type === 'manual'
                        ? $this->normalizeFields($this->toArray($method->fields))
                        : [],
                    'gateway_code' => $method->gateway->gateway_code ?? null,
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $methods,
        ]);
    }

    public function accounts(Request $request): \Illuminate\Http\JsonResponse
    {
        $accounts = WithdrawAccount::with('method.gateway')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(function (WithdrawAccount $account) {
                return [
                    'id' => $account->id,
                    'withdraw_method_id' => $account->withdraw_method_id,
                    'method_name' => $account->method_name,
                    'credentials' => $this->toArray($account->credentials),
                    'method' => [
                        'id' => $account->method->id,
                        'name' => $account->method->name,
                        'currency' => $account->method->currency,
                        'type' => $account->method->type,
                        'charge' => (float) $account->method->charge,
                        'charge_type' => $account->method->charge_type,
                        'min_withdraw' => (float) $account->method->min_withdraw,
                        'max_withdraw' => (float) $account->method->max_withdraw,
                        'rate' => (float) $account->method->rate,
                        'gateway_code' => $account->method->gateway->gateway_code ?? null,
                    ],
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $accounts,
        ]);
    }

    public function createAccount(Request $request): \Illuminate\Http\JsonResponse
    {
        if (! setting('kyc_withdraw') && ! $request->user()->kyc) {
            return response()->json(['status' => false, 'message' => 'Please verify your KYC.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'withdraw_method_id' => 'required|integer|exists:withdraw_methods,id',
            'method_name' => 'nullable|string|max:255',
            'credentials' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $method = WithdrawMethod::where('status', true)
            ->find($request->integer('withdraw_method_id'));

        if (! $method) {
            return response()->json(['status' => false, 'message' => 'Withdraw method is unavailable.'], 422);
        }

        $credentialsInput = $this->resolveCredentialsInput($request);
        $credentialResult = $this->buildCredentials($request, $method, $credentialsInput);

        if (! $credentialResult['ok']) {
            return response()->json(['status' => false, 'message' => $credentialResult['message']], 422);
        }

        $account = WithdrawAccount::create([
            'user_id' => $request->user()->id,
            'withdraw_method_id' => $method->id,
            'method_name' => $request->filled('method_name')
                ? $request->string('method_name')->toString()
                : ($method->name . '-' . $method->currency),
            'credentials' => json_encode($credentialResult['credentials']),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Withdraw account created successfully.',
            'data' => [
                'id' => $account->id,
                'withdraw_method_id' => $account->withdraw_method_id,
                'method_name' => $account->method_name,
                'credentials' => $credentialResult['credentials'],
            ],
        ]);
    }

    public function updateAccount(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $account = WithdrawAccount::where('user_id', $request->user()->id)->find($id);
        if (! $account) {
            return response()->json(['status' => false, 'message' => 'Withdraw account not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'withdraw_method_id' => 'required|integer|exists:withdraw_methods,id',
            'method_name' => 'nullable|string|max:255',
            'credentials' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $method = WithdrawMethod::where('status', true)
            ->find($request->integer('withdraw_method_id'));

        if (! $method) {
            return response()->json(['status' => false, 'message' => 'Withdraw method is unavailable.'], 422);
        }

        $credentialsInput = $this->resolveCredentialsInput($request);
        $oldCredentials = $this->toArray($account->credentials);

        $credentialResult = $this->buildCredentials($request, $method, $credentialsInput, $oldCredentials);
        if (! $credentialResult['ok']) {
            return response()->json(['status' => false, 'message' => $credentialResult['message']], 422);
        }

        $account->update([
            'withdraw_method_id' => $method->id,
            'method_name' => $request->filled('method_name')
                ? $request->string('method_name')->toString()
                : ($method->name . '-' . $method->currency),
            'credentials' => json_encode($credentialResult['credentials']),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Withdraw account updated successfully.',
            'data' => [
                'id' => $account->id,
                'withdraw_method_id' => $account->withdraw_method_id,
                'method_name' => $account->method_name,
                'credentials' => $credentialResult['credentials'],
            ],
        ]);
    }

    public function deleteAccount(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $account = WithdrawAccount::where('user_id', $request->user()->id)->find($id);
        if (! $account) {
            return response()->json(['status' => false, 'message' => 'Withdraw account not found.'], 404);
        }

        $account->delete();

        return response()->json([
            'status' => true,
            'message' => 'Withdraw account deleted successfully.',
        ]);
    }

    public function details(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'withdraw_account_id' => 'required|integer',
            'amount' => ['nullable', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $account = WithdrawAccount::with('method')
            ->where('user_id', $request->user()->id)
            ->find($request->integer('withdraw_account_id'));

        if (! $account || ! $account->method || ! $account->method->status) {
            return response()->json(['status' => false, 'message' => 'Withdraw account not found or inactive.'], 404);
        }

        $amount = (float) $request->get('amount', 0);
        $method = $account->method;
        $siteCurrency = setting('site_currency', 'global');

        $charge = (float) $method->charge;
        if ($method->charge_type !== 'fixed') {
            $charge = ($charge / 100) * $amount;
        }

        return response()->json([
            'status' => true,
            'data' => [
                'withdraw_account_id' => $account->id,
                'name' => $account->method_name,
                'charge' => (float) $charge,
                'charge_type' => $method->charge_type,
                'amount' => $amount,
                'total_amount' => $amount + (float) $charge,
                'range' => [
                    'min' => (float) $method->min_withdraw,
                    'max' => (float) $method->max_withdraw,
                    'currency' => $siteCurrency,
                ],
                'processing_time' => (int) $method->required_time > 0
                    ? ('Processing Time: ' . $method->required_time . $method->required_time_format)
                    : 'This Is Automatic Method',
                'rate' => (float) $method->rate,
                'pay_currency' => $method->currency,
                'conversion_rate' => $method->currency !== $siteCurrency
                    ? ('1 ' . $siteCurrency . ' = ' . $method->rate . ' ' . $method->currency)
                    : null,
                'pay_amount' => $amount * (float) $method->rate,
                'credentials' => $this->toArray($account->credentials),
                'logo' => $method->icon,
            ],
        ]);
    }

    public function initiate(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        if (! setting('user_withdraw', 'permission') || ! $user->withdraw_status) {
            return response()->json(['status' => false, 'message' => 'Withdraw currently unavailable.'], 403);
        }

        $withdrawOffDays = WithdrawalSchedule::where('status', 0)->pluck('name')->toArray();
        if (in_array(Carbon::now()->format('l'), $withdrawOffDays, true)) {
            return response()->json(['status' => false, 'message' => 'Today is the off day of withdraw.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'regex:/^[0-9]+(\.[0-9][0-9]?)?$/'],
            'withdraw_account_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $todayTransaction = Transaction::whereIn('type', [TxnType::Withdraw, TxnType::WithdrawAuto])
            ->whereDate('created_at', Carbon::today())
            ->count();

        $dayLimit = (float) setting('withdraw_day_limit', 'fee');
        if ($todayTransaction >= $dayLimit) {
            return response()->json(['status' => false, 'message' => 'Today withdraw limit has been reached.'], 422);
        }

        $amount = (float) $request->amount;
        $account = WithdrawAccount::with('method.gateway')
            ->where('user_id', $user->id)
            ->find($request->integer('withdraw_account_id'));

        if (! $account || ! $account->method || ! $account->method->status) {
            return response()->json(['status' => false, 'message' => 'Withdraw account not found or inactive.'], 404);
        }

        $method = $account->method;

        if ($amount < $method->min_withdraw || $amount > $method->max_withdraw) {
            $currencySymbol = setting('currency_symbol', 'global');
            $message = 'Please withdraw the amount within the range '
                . $currencySymbol . $method->min_withdraw
                . ' to ' . $currencySymbol . $method->max_withdraw;

            return response()->json(['status' => false, 'message' => $message], 422);
        }

        $charge = $method->charge_type === 'percentage'
            ? (($method->charge / 100) * $amount)
            : (float) $method->charge;

        $totalAmount = $amount + (float) $charge;

        if ($user->balance < $totalAmount) {
            return response()->json(['status' => false, 'message' => 'Insufficient balance.'], 422);
        }

        $user->decrement('balance', $totalAmount);

        $payAmount = $amount * (float) $method->rate;
        $type = $method->type === 'auto' ? TxnType::WithdrawAuto : TxnType::Withdraw;

        $txnInfo = Txn::new(
            $amount,
            $charge,
            $totalAmount,
            $method->name,
            'Withdraw With ' . $account->method_name,
            $type,
            TxnStatus::Pending,
            $method->currency,
            $payAmount,
            $user->id,
            null,
            'User',
            $this->toArray($account->credentials)
        );

        if ($method->type === 'auto') {
            $gatewayCode = $method->gateway->gateway_code ?? null;
            if ($gatewayCode) {
                try {
                    $this->withdrawAutoGateway($gatewayCode, $txnInfo);
                } catch (\Throwable $e) {
                    \Log::error('API withdraw auto gateway error: ' . $e->getMessage(), [
                        'tnx' => $txnInfo->tnx,
                        'gateway' => $gatewayCode,
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'type' => 'auto',
                'tnx' => $txnInfo->tnx,
                'amount' => $amount,
                'charge' => (float) $charge,
                'final_amount' => (float) $totalAmount,
                'currency' => $method->currency,
                'message' => 'Withdrawal request created and sent for automatic processing.',
            ]);
        }

        return response()->json([
            'status' => true,
            'type' => 'manual',
            'tnx' => $txnInfo->tnx,
            'amount' => $amount,
            'charge' => (float) $charge,
            'final_amount' => (float) $totalAmount,
            'pay_amount' => (float) $payAmount,
            'currency' => $method->currency,
            'message' => 'Withdrawal request submitted successfully.',
        ]);
    }

    public function status(Request $request, string $tnx): \Illuminate\Http\JsonResponse
    {
        $transaction = Transaction::where('user_id', $request->user()->id)
            ->where('tnx', $tnx)
            ->whereIn('type', [TxnType::Withdraw, TxnType::WithdrawAuto])
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => [
                'tnx' => $transaction->tnx,
                'amount' => (float) $transaction->amount,
                'charge' => (float) $transaction->charge,
                'final_amount' => (float) $transaction->final_amount,
                'pay_amount' => (float) $transaction->pay_amount,
                'pay_currency' => $transaction->pay_currency,
                'method' => $transaction->method,
                'txn_type' => $transaction->type instanceof \BackedEnum
                    ? $transaction->type->value
                    : $transaction->type,
                'txn_status' => $transaction->status instanceof \BackedEnum
                    ? $transaction->status->value
                    : $transaction->status,
                'created_at' => $transaction->attributes['created_at'],
            ],
        ]);
    }

    private function resolveCredentialsInput(Request $request): array
    {
        $credentials = $request->input('credentials', []);

        if (is_string($credentials)) {
            $decoded = json_decode($credentials, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($credentials) ? $credentials : [];
    }

    private function buildCredentials(Request $request, WithdrawMethod $method, array $credentials, array $oldCredentials = []): array
    {
        $fields = $this->normalizeFields($this->toArray($method->fields));
        $parsed = [];

        foreach ($fields as $field) {
            $name = (string) ($field['label'] ?? $field['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $type = strtolower((string) ($field['type'] ?? 'text'));
            $validation = strtolower(trim((string) ($field['validation'] ?? 'required')));
            $validation = $validation === 'required' ? 'required' : 'nullable';
            $isRequired = $validation === 'required';

            $raw = data_get($credentials, $name . '.value');
            if ($raw === null) {
                $raw = data_get($credentials, $name);
            }

            $fileKey = 'credentials.' . $name . '.value';
            $uploaded = $request->file($fileKey);

            if ($type === 'file') {
                if ($uploaded) {
                    if (! in_array(strtolower($uploaded->getClientOriginalExtension()), ['jpeg', 'png', 'jpg', 'gif', 'svg'], true)) {
                        return ['ok' => false, 'message' => $name . ' accepts only jpeg, png, jpg, gif, svg files.'];
                    }
                    if ($uploaded->getSize() > 5100000) {
                        return ['ok' => false, 'message' => $name . ' max file size is 5MB.'];
                    }

                    $oldPath = data_get($oldCredentials, $name . '.value');
                    $raw = $this->imageUploadTrait($uploaded, is_string($oldPath) ? $oldPath : null);
                } elseif (($raw === null || $raw === '') && $isRequired) {
                    $raw = data_get($oldCredentials, $name . '.value');
                }
            }

            if (($raw === null || $raw === '') && ! $isRequired) {
                $raw = data_get($oldCredentials, $name . '.value');
            }

            if (($raw === null || $raw === '') && $isRequired) {
                return ['ok' => false, 'message' => $name . ' field is required.'];
            }

            $parsed[$name] = [
                'type' => $type,
                'validation' => $validation,
                'value' => $raw,
            ];
        }

        return ['ok' => true, 'credentials' => $parsed];
    }

    private function normalizeFields(array $fields): array
    {
        $normalized = [];

        foreach ($fields as $key => $field) {
            if (! is_array($field)) {
                continue;
            }

            $name = trim((string) ($field['name'] ?? $key ?? ''));
            if ($name === '') {
                continue;
            }

            $type = strtolower(trim((string) ($field['type'] ?? 'text')));
            $validation = strtolower(trim((string) ($field['validation'] ?? 'required')));

            $normalized[] = [
                'label' => $name,
                'name' => $name,
                'type' => $type !== '' ? $type : 'text',
                'validation' => $validation === 'required' ? 'required' : 'nullable',
            ];
        }

        return $normalized;
    }

    private function toArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}

