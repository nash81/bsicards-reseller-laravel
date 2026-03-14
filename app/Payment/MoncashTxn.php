<?php

namespace App\Payment;

use App\Models\Transaction;
use App\Services\MoncashService;
use Illuminate\Support\Facades\Log;
use Payment\Transaction\BaseTxn;

class MoncashTxn extends BaseTxn
{
    public function __construct($txnInfo)
    {
        parent::__construct($txnInfo);
    }

    public function deposit()
    {
        try {
            $client = new MoncashService();

            $payload = [
                'amount' => (float) number_format($this->amount, 2, '.', ''),
                'orderId' => $this->txn,
                'currency' => $this->currency !== '' ? $this->currency : 'HTG',
                'redirectUrl' => route('ipn.moncash', ['reftrn' => $this->txn]),
            ];

            $response = $client->createPayment($payload);
            $checkoutUrl = $client->extractCheckoutUrl($response);
            $providerRef = $client->extractProviderReference($response);

            if ($providerRef) {
                Transaction::tnx($this->txn)?->update([
                    'approval_cause' => $providerRef,
                ]);
            }

            if ($checkoutUrl) {
                return redirect($checkoutUrl);
            }

            Log::warning('MonCash createPayment did not return a checkout URL.', [
                'txn' => $this->txn,
                'base_url' => $client->getBaseUrl(),
                'response' => $response,
            ]);
            tnotify('error', __('MonCash did not return a checkout URL. Please verify API credentials and mode.'));
        } catch (\Throwable $exception) {
            Log::error('MonCash payment initialization failed.', [
                'txn' => $this->txn,
                'error' => $exception->getMessage(),
            ]);
            tnotify('error', __('MonCash payment initialization failed. Please check gateway credentials.'));
        }

        return redirect()->route('status.cancel');
    }
}
