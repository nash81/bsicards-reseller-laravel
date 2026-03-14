<?php

namespace App\Services;

use App\Models\Gateway;
use Exception;
use Illuminate\Support\Facades\Http;

class MoncashService
{
    private array $credentials;

    private string $baseUrl;

    public function __construct()
    {
        $gateway = Gateway::code('moncash')->first(['credentials']);
        if (! $gateway) {
            throw new Exception('MonCash gateway credentials not found.');
        }

        $this->credentials = json_decode($gateway->credentials, true) ?? [];

        $mode = strtolower((string) ($this->credentials['mode'] ?? 'production'));
        $sandboxUrl = 'https://sandbox.moncashbutton.digicelgroup.com/Api';
        $productionUrl = 'https://moncashbutton.digicelgroup.com/Api';

        $resolvedBaseUrl = (string) ($this->credentials['base_url']
            ?? $this->credentials['baseUrl']
            ?? ($mode === 'sandbox' ? $sandboxUrl : $productionUrl));

        $this->baseUrl = rtrim($resolvedBaseUrl, '/');
    }

    public function createPayment(array $payload): array
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->post($this->baseUrl.'/v1/CreatePayment', $payload);

        return $response->json() ?? [];
    }

    public function retrieveByOrderId(string $orderId): array
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->acceptJson()
            ->get($this->baseUrl.'/v1/RetrieveOrderPayment/'.urlencode($orderId));

        return $response->json() ?? [];
    }

    public function retrieveByTransactionId(string $transactionId): array
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->acceptJson()
            ->get($this->baseUrl.'/v1/RetrieveTransactionPayment/'.urlencode($transactionId));

        return $response->json() ?? [];
    }

    public function extractCheckoutUrl(array $response): ?string
    {
        $candidates = [
            data_get($response, 'payment_token.payment_url'),
            data_get($response, 'payment_token.url'),
            data_get($response, 'paymentUrl'),
            data_get($response, 'redirect_url'),
            data_get($response, 'data.payment_url'),
            data_get($response, 'data.redirect_url'),
        ];

        foreach ($candidates as $value) {
            if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
                return $value;
            }
        }

        $tokenCandidates = [
            data_get($response, 'payment_token.token'),
            data_get($response, 'payment_token.payment_token'),
            data_get($response, 'payment_token'),
            data_get($response, 'token'),
        ];

        foreach ($tokenCandidates as $token) {
            if (is_scalar($token) && (string) $token !== '') {
                return $this->webBaseUrl().'/Moncash-middleware/Payment/Redirect?token='.urlencode((string) $token);
            }
        }

        return null;
    }

    public function extractProviderReference(array $response): ?string
    {
        $candidates = [
            data_get($response, 'payment_token.token'),
            data_get($response, 'payment_token.payment_token'),
            data_get($response, 'transaction_id'),
            data_get($response, 'transactionId'),
        ];

        foreach ($candidates as $value) {
            if (is_scalar($value) && (string) $value !== '') {
                return (string) $value;
            }
        }

        return null;
    }

    public function isPaid(array $response): bool
    {
        $status = strtolower((string) (data_get($response, 'payment.status')
            ?? data_get($response, 'status')
            ?? data_get($response, 'state')
            ?? ''));

        $message = strtolower((string) (data_get($response, 'message') ?? ''));

        return in_array($status, ['completed', 'success', 'succeeded', 'successful', 'paid'], true)
            || str_contains($message, 'success');
    }

    public function testConnection(): array
    {
        $token = $this->getAccessToken();

        return [
            'connected' => $token !== '',
            'base_url' => $this->baseUrl,
            'mode' => strtolower((string) ($this->credentials['mode'] ?? 'production')),
        ];
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    private function webBaseUrl(): string
    {
        return preg_replace('#/Api$#', '', $this->baseUrl) ?: $this->baseUrl;
    }

    private function getAccessToken(): string
    {
        $clientId = (string) ($this->credentials['clientId'] ?? $this->credentials['client_id'] ?? '');
        $clientSecret = (string) ($this->credentials['clientSecret'] ?? $this->credentials['client_secret'] ?? '');

        if ($clientId === '' || $clientSecret === '') {
            throw new Exception('MonCash clientId/clientSecret missing from gateway credentials.');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Basic '.base64_encode($clientId.':'.$clientSecret),
            'Accept' => 'application/json',
        ])->asForm()->post($this->baseUrl.'/oauth/token', [
            'grant_type' => 'client_credentials',
            'scope' => 'read,write',
        ]);

        $token = (string) (data_get($response->json(), 'access_token') ?? '');

        if ($token === '') {
            throw new Exception('Unable to authenticate with MonCash API.');
        }

        return $token;
    }
}

