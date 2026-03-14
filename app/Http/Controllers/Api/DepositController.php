<?php

namespace App\Http\Controllers\Api;

use App\Enums\TxnStatus;
use App\Enums\TxnType;
use App\Enums\GatewayType;
use App\Http\Controllers\Controller;
use App\Models\DepositMethod;
use App\Models\Transaction;
use App\Services\MoncashService;
use App\Traits\Payment;
use App\Traits\NotifyTrait;
use Payment\Paypal\PaypalTxn;
use Payment\Stripe\StripeTxn;
use Payment\Mollie\MollieTxn;
use Payment\Perfectmoney\PerfectmoneyTxn;
use Payment\Coinbase\CoinbaseTxn;
use Payment\Flutterwave\FlutterwaveTxn;
use Payment\Cryptomus\CryptomusTxn;
use Payment\Nowpayments\NowpaymentsTxn;
use Payment\Securionpay\SecurionpayTxn;
use Payment\Coingate\CoingateTxn;
use Payment\Voguepay\VoguepayTxn;
use Payment\Paystack\PaystackTxn;
use Payment\Monnify\MonnifyTxn;
use Payment\Coinpayments\CoinpaymentsTxn;
use Payment\Paymongo\PaymongoTxn;
use Payment\Coinremitter\CoinremitterTxn;
use Payment\Btcpayserver\BtcpayserverTxn;
use Payment\Binance\BinanceTxn;
use Payment\Cashmaal\CashmaalTxn;
use Payment\BlockIo\BlockIoTxn;
use Payment\Blockchain\BlockchainTxn;
use Payment\Instamojo\InstamojoTxn;
use Payment\Paytm\PaytmTxn;
use Payment\Razorpay\RazorpayTxn;
use Payment\Twocheckout\TwocheckoutTxn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Txn;

class DepositController extends Controller
{
    use Payment, NotifyTrait;

    /**
     * List all active deposit gateways
     */
    public function gateways(Request $request)
    {
        $gateways = DepositMethod::where('status', 1)
            ->get()
            ->map(fn($g) => [
                'id'              => $g->id,
                'name'            => $g->name,
                'gateway_code'    => $g->gateway_code,
                'logo'            => $g->gateway_logo,
                'currency'        => $g->currency,
                'minimum_deposit' => (float) $g->minimum_deposit,
                'maximum_deposit' => (float) $g->maximum_deposit,
                'charge'          => (float) $g->charge,
                'charge_type'     => $g->charge_type,
                'rate'            => (float) $g->rate,
                'type'            => $g->type,   // auto / manual
            ]);

        return response()->json([
            'status' => true,
            'data'   => $gateways,
        ]);
    }

    /**
     * Initiate a deposit.
     *
     * For automatic gateways (e.g. MonCash, Stripe, PayPal) this returns
     * a `redirect_url` which the Flutter app should open in an in-app WebView
     * or external browser. The IPN/webhook at the server side will confirm the
     * payment and update the transaction status.
     *
     * For manual gateways it returns payment instructions and a transaction
     * reference so the user can upload proof later.
     */
    public function initiate(Request $request)
    {
        $user = $request->user();

        if (! setting('user_deposit', 'permission') || ! $user->deposit_status) {
            return response()->json(['status' => false, 'message' => 'Deposits are currently unavailable.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'gateway_code' => 'required|string',
            'amount'       => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $gatewayInfo = DepositMethod::code($request->gateway_code)->first();
        if (! $gatewayInfo) {
            return response()->json(['status' => false, 'message' => 'Gateway not found.'], 404);
        }

        $amount = (float) $request->amount;
        if ($amount < $gatewayInfo->minimum_deposit || $amount > $gatewayInfo->maximum_deposit) {
            $sym     = setting('currency_symbol', 'global');
            return response()->json([
                'status'  => false,
                'message' => "Amount must be between {$sym}{$gatewayInfo->minimum_deposit} and {$sym}{$gatewayInfo->maximum_deposit}.",
            ], 422);
        }

        $charge      = $gatewayInfo->charge_type === 'percentage'
            ? ($gatewayInfo->charge / 100) * $amount
            : (float) $gatewayInfo->charge;
        $finalAmount = $amount + $charge;
        $payAmount   = $finalAmount * $gatewayInfo->rate;

        $txnInfo = Txn::new(
            $amount, $charge, $finalAmount,
            $gatewayInfo->gateway_code,
            'Deposit With ' . $gatewayInfo->name,
            TxnType::Deposit,
            TxnStatus::Pending,
            $gatewayInfo->currency,
            $payAmount,
            $user->id
        );

        // Manual gateway – return payment details
        $gatewayCode = $gatewayInfo->gateway->gateway_code ?? $request->gateway_code;
        if ($gatewayInfo->type === GatewayType::Manual->value) {
            return response()->json([
                'status'          => true,
                'type'            => 'manual',
                'tnx'             => $txnInfo->tnx,
                'amount'          => $amount,
                'charge'          => $charge,
                'final_amount'    => $finalAmount,
                'pay_amount'      => $payAmount,
                'currency'        => $gatewayInfo->currency,
                'payment_details' => $gatewayInfo->payment_details ?? null,
                'field_options'   => $gatewayInfo->field_options ?? null,
                'message'         => 'Submit payment proof to complete your deposit.',
            ]);
        }

        // Automatic gateway – obtain redirect URL
        $redirectUrl = $this->getAutoGatewayUrl($gatewayCode, $txnInfo, $request->get('return_url'));

        if ($redirectUrl) {
            return response()->json([
                'status'       => true,
                'type'         => 'auto',
                'tnx'          => $txnInfo->tnx,
                'redirect_url' => $redirectUrl,
                'amount'       => $amount,
                'currency'     => $gatewayInfo->currency,
                'message'      => 'Open the redirect_url in a WebView to complete payment.',
            ]);
        }

        // Pending (e.g. crypto waiting for confirmations)
        return response()->json([
            'status'   => true,
            'type'     => 'pending',
            'tnx'      => $txnInfo->tnx,
            'amount'   => $amount,
            'currency' => $gatewayInfo->currency,
            'message'  => 'Your deposit is pending confirmation.',
        ]);
    }

    /**
     * Check the current status of a deposit by transaction reference
     */
    public function status(Request $request, string $tnx)
    {
        $transaction = Transaction::where('user_id', $request->user()->id)
            ->where('tnx', $tnx)
            ->firstOrFail();

        return response()->json([
            'status'     => true,
            'tnx'        => $transaction->tnx,
            'amount'     => (float) $transaction->amount,
            'final_amount' => (float) $transaction->final_amount,
            'method'     => $transaction->method,
            'txn_status' => $transaction->status instanceof \BackedEnum
                ? $transaction->status->value
                : $transaction->status,
            'created_at' => $transaction->attributes['created_at'],
        ]);
    }

    /**
     * Submit manual deposit proof (file upload or field data)
     */
    public function submitManualProof(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tnx'   => 'required|string',
            'proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $transaction = Transaction::where('user_id', $request->user()->id)
            ->where('tnx', $request->tnx)
            ->whereIn('type', [TxnType::ManualDeposit, TxnType::Deposit])
            ->firstOrFail();

        $manualData = [];
        if ($request->hasFile('proof')) {
            $path = $request->file('proof')->store('manual_deposits', 'public');
            $manualData['proof'] = $path;
        }
        if ($request->filled('manual_fields')) {
            $manualData = array_merge($manualData, $request->get('manual_fields', []));
        }

        $transaction->update([
            'manual_field_data' => json_encode($manualData),
            'type'              => TxnType::ManualDeposit,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Proof submitted. Your deposit will be reviewed shortly.',
        ]);
    }

    // -------------------------------------------------------
    // Private: get redirect URL from automatic gateway
    // -------------------------------------------------------
    private function getAutoGatewayUrl($gatewayCode, $txnInfo, ?string $returnUrl = null): ?string
    {
        // MonCash – special handling with dedicated service
        if ($gatewayCode === 'moncash') {
            try {
                $client  = new MoncashService();
                $payload = [
                    'amount'      => (float) number_format($txnInfo->amount, 2, '.', ''),
                    'orderId'     => $txnInfo->tnx,
                    'currency'    => $txnInfo->pay_currency ?: 'HTG',
                    'redirectUrl' => route('ipn.moncash', ['reftrn' => $txnInfo->tnx]),
                ];
                $response    = $client->createPayment($payload);
                $checkoutUrl = $client->extractCheckoutUrl($response);

                if ($checkoutUrl) {
                    $providerRef = $client->extractProviderReference($response);
                    if ($providerRef) {
                        Transaction::tnx($txnInfo->tnx)?->update(['approval_cause' => $providerRef]);
                    }
                    return $checkoutUrl;
                }
            } catch (\Throwable $e) {
                \Log::error('API MonCash init error: ' . $e->getMessage());
            }
            return null;
        }

        // For all other gateways: instantiate the gateway transaction class directly
        try {
            $gatewayMap = [
                'paypal' => PaypalTxn::class,
                'stripe' => StripeTxn::class,
                'mollie' => MollieTxn::class,
                'perfectmoney' => PerfectmoneyTxn::class,
                'coinbase' => CoinbaseTxn::class,
                'flutterwave' => FlutterwaveTxn::class,
                'cryptomus' => CryptomusTxn::class,
                'nowpayments' => NowpaymentsTxn::class,
                'securionpay' => SecurionpayTxn::class,
                'coingate' => CoingateTxn::class,
                'voguepay' => VoguepayTxn::class,
                'monnify' => MonnifyTxn::class,
                'coinpayments' => CoinpaymentsTxn::class,
                'paymongo' => PaymongoTxn::class,
                'coinremitter' => CoinremitterTxn::class,
                'btcpayserver' => BtcpayserverTxn::class,
                'binance' => BinanceTxn::class,
                'cashmaal' => CashmaalTxn::class,
                'blockio' => BlockIoTxn::class,
                'blockchain' => BlockchainTxn::class,
                'instamojo' => InstamojoTxn::class,
                'paytm' => PaytmTxn::class,
                'paystack' => PaystackTxn::class,
                'razorpay' => RazorpayTxn::class,
                'twocheckout' => TwocheckoutTxn::class,
            ];

            if (!isset($gatewayMap[$gatewayCode])) {
                \Log::warning("Gateway [$gatewayCode] not in supported map");
                return null;
            }

            $txnClass = $gatewayMap[$gatewayCode];
            \Log::info("Instantiating gateway [$gatewayCode] with class: " . $txnClass);

            $txnHandler = app($txnClass, ['txnInfo' => $txnInfo]);

            if (!$txnHandler) {
                \Log::error("Failed to instantiate gateway class: " . $txnClass);
                return null;
            }

            \Log::info("Calling deposit() method on " . $gatewayCode);

            // Call deposit() which should return a RedirectResponse for automatic gateways
            $response = $txnHandler->deposit();

            if ($response instanceof \Illuminate\Http\RedirectResponse) {
                $url = $response->getTargetUrl();
                \Log::info("Gateway [$gatewayCode] returned redirect URL: " . $url);
                return $url;
            }

            $responseType = is_object($response) ? get_class($response) : gettype($response);
            \Log::warning("Gateway [$gatewayCode] returned non-RedirectResponse: " . $responseType . " | Content: " . json_encode($response));
            return null;

        } catch (\Throwable $e) {
            \Log::error("Gateway [$gatewayCode] exception: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return null;
    }
}


