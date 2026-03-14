<?php

namespace Payment\Moncash;

use App\Models\Transaction;
use App\Services\MoncashService;
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
        } catch (\Throwable $exception) {
            // Fallback to cancel route if provider API is unreachable or payload fails.
        }

        return redirect()->route('status.cancel');
    }
}
