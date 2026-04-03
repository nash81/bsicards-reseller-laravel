<?php

namespace BSICards;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * BSICARDS PHP SDK Client
 *
 * Main client for interacting with BSICARDS Card Issuance API
 */
class BSICardsClient
{
    private $client;
    private $publicKey;
    private $secretKey;

    /**
     * Initialize the SDK client
     *
     * @param string|null $publicKey Public API key
     * @param string|null $secretKey Secret API key
     * @param array $config Additional Guzzle configuration
     *
     * @throws \InvalidArgumentException If keys are not provided
     */
    public function __construct(?string $publicKey = null, ?string $secretKey = null, array $config = [])
    {
        $this->publicKey = $publicKey ?? $this->getEnvVar('BSICARDS_PUBLIC_KEY');
        $this->secretKey = $secretKey ?? $this->getEnvVar('BSICARDS_SECRET_KEY');

        if (!$this->publicKey || !$this->secretKey) {
            throw new \InvalidArgumentException(
                'BSICARDS Public Key and Secret Key are required. ' .
                'Provide them as constructor arguments or set BSICARDS_PUBLIC_KEY and BSICARDS_SECRET_KEY environment variables.'
            );
        }

        $apiBaseUrl = (string) $this->getEnvVar('API_ENDPOINT', '');
        if ($apiBaseUrl === '') {
            throw new \InvalidArgumentException('API_ENDPOINT environment variable is required.');
        }
        $apiBaseUrl = rtrim($apiBaseUrl, '/') . '/';

        $clientConfig = array_merge([
            'base_uri' => $apiBaseUrl,
            'timeout' => 30,
            'connect_timeout' => 10,
        ], $config);

        $this->client = new Client($clientConfig);
    }

    /**
     * Get environment variable
     */
    private function getEnvVar(string $key, $default = null)
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
    }

    /**
     * Get common request headers
     */
    private function getHeaders(): array
    {
        return [
            'publickey' => $this->publicKey,
            'secretkey' => $this->secretKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Make HTTP POST request
     */
    private function post(string $endpoint, array $body = []): array
    {
        try {
            $response = $this->client->post($endpoint, [
                'headers' => $this->getHeaders(),
                'json' => $body,
            ]);

            return json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (GuzzleException $e) {
            throw new APIException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Make HTTP GET request
     */
    private function get(string $endpoint, array $query = []): array
    {
        try {
            $response = $this->client->get($endpoint, [
                'headers' => $this->getHeaders(),
                'query' => $query,
            ]);

            return json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (GuzzleException $e) {
            throw new APIException($e->getMessage(), $e->getCode(), $e);
        }
    }

    // ========================
    // MASTERCARD ISSUANCE
    // ========================

    /**
     * Create a new MasterCard
     *
     * @param string $userEmail User email
     * @param string $nameOnCard Name on card
     * @param string $pin Card PIN
     *
     * @return array API response
     * @throws APIException
     */
    public function mastercardCreateCard(string $userEmail, string $nameOnCard, string $pin): array
    {
        return $this->post('newcard', [
            'useremail' => $userEmail,
            'nameoncard' => $nameOnCard,
            'pin' => $pin,
        ]);
    }

    /**
     * Get all MasterCards for a user
     *
     * @param string $userEmail User email
     *
     * @return array API response
     * @throws APIException
     */
    public function mastercardGetAllCards(string $userEmail): array
    {
        return $this->post('getallcard', [
            'useremail' => $userEmail,
        ]);
    }

    /**
     * Get pending MasterCards for a user
     *
     * @param string $userEmail User email
     *
     * @return array API response
     * @throws APIException
     */
    public function mastercardGetPendingCards(string $userEmail): array
    {
        return $this->post('getpendingcards', [
            'useremail' => $userEmail,
        ]);
    }

    /**
     * Get specific MasterCard details
     *
     * @param string $userEmail User email
     * @param string $cardId Card ID
     *
     * @return array API response
     * @throws APIException
     */
    public function mastercardGetCard(string $userEmail, string $cardId): array
    {
        return $this->post('getcard', [
            'useremail' => $userEmail,
            'cardid' => $cardId,
        ]);
    }

    /**
     * Get MasterCard transactions
     *
     * @param string $userEmail User email
     * @param string $cardId Card ID
     *
     * @return array API response
     * @throws APIException
     */
    public function mastercardGetTransactions(string $userEmail, string $cardId): array
    {
        return $this->post('getcardtransactions', [
            'useremail' => $userEmail,
            'cardid' => $cardId,
        ]);
    }

    /**
     * Change MasterCard PIN
     *
     * @param string $userEmail User email
     * @param string $cardId Card ID
     * @param string $newPin New PIN
     *
     * @return array API response
     * @throws APIException
     */
    public function mastercardChangePin(string $userEmail, string $cardId, string $newPin): array
    {
        return $this->post('changepin', [
            'useremail' => $userEmail,
            'cardid' => $cardId,
            'pin' => $newPin,
        ]);
    }

    /**
     * Freeze (block) a MasterCard
     *
     * @param string $userEmail User email
     * @param string $cardId Card ID
     *
     * @return array API response
     * @throws APIException
     */
    public function mastercardFreezeCard(string $userEmail, string $cardId): array
    {
        return $this->post('blockcard', [
            'useremail' => $userEmail,
            'cardid' => $cardId,
        ]);
    }

    /**
     * Unfreeze (unblock) a MasterCard
     *
     * @param string $userEmail User email
     * @param string $cardId Card ID
     *
     * @return array API response
     * @throws APIException
     */
    public function mastercardUnfreezeCard(string $userEmail, string $cardId): array
    {
        return $this->post('unblockcard', [
            'useremail' => $userEmail,
            'cardid' => $cardId,
        ]);
    }

    /**
     * Fund a MasterCard
     *
     * @param string $userEmail User email
     * @param string $cardId Card ID
     * @param string $amount Amount (minimum $10.00)
     *
     * @return array API response
     * @throws APIException
     */
    public function mastercardFundCard(string $userEmail, string $cardId, string $amount): array
    {
        return $this->post('fundcard', [
            'useremail' => $userEmail,
            'cardid' => $cardId,
            'amount' => $amount,
        ]);
    }

    // ========================
    // VISA ISSUANCE
    // ========================

    /**
     * Create a new Visa Card
     *
     * @param string $userEmail User email
     * @param string $nameOnCard Name on card
     * @param string $nationalIdNumber National ID number
     * @param string $nationalIdImage National ID image URL
     * @param string $userPhoto User photo URL
     * @param string $dateOfBirth Date of birth (YYYY-MM-DD)
     *
     * @return array API response
     * @throws APIException
     */
    public function visaCreateCard(
        string $userEmail,
        string $nameOnCard,
        string $nationalIdNumber,
        string $nationalIdImage,
        string $userPhoto,
        string $dateOfBirth
    ): array {
        return $this->post('visanewcard', [
            'useremail' => $userEmail,
            'nameoncard' => $nameOnCard,
            'nationalidnumber' => $nationalIdNumber,
            'nationalidimage' => $nationalIdImage,
            'userphoto' => $userPhoto,
            'dob' => $dateOfBirth,
        ]);
    }

    /**
     * Get all Visa cards for a user
     *
     * @param string $userEmail User email
     *
     * @return array API response
     * @throws APIException
     */
    public function visaGetAllCards(string $userEmail): array
    {
        return $this->post('visagetallcard', [
            'useremail' => $userEmail,
        ]);
    }

    /**
     * Get pending Visa cards for a user
     *
     * @param string $userEmail User email
     *
     * @return array API response
     * @throws APIException
     */
    public function visaGetPendingCards(string $userEmail): array
    {
        return $this->post('visagetpendingcards', [
            'useremail' => $userEmail,
        ]);
    }

    /**
     * Get specific Visa card details
     *
     * @param string $userEmail User email
     * @param string $cardId Card ID
     *
     * @return array API response
     * @throws APIException
     */
    public function visaGetCard(string $userEmail, string $cardId): array
    {
        return $this->post('visagetcard', [
            'useremail' => $userEmail,
            'cardid' => $cardId,
        ]);
    }

    /**
     * Get Visa card transactions
     *
     * @param string $userEmail User email
     * @param string $cardId Card ID
     *
     * @return array API response
     * @throws APIException
     */
    public function visaGetTransactions(string $userEmail, string $cardId): array
    {
        return $this->post('visagetcardtransactions', [
            'useremail' => $userEmail,
            'cardid' => $cardId,
        ]);
    }

    /**
     * Freeze (block) a Visa card
     *
     * @param string $userEmail User email
     * @param string $cardId Card ID
     *
     * @return array API response
     * @throws APIException
     */
    public function visaFreezeCard(string $userEmail, string $cardId): array
    {
        return $this->post('visablockcard', [
            'useremail' => $userEmail,
            'cardid' => $cardId,
        ]);
    }

    /**
     * Unfreeze (unblock) a Visa card
     *
     * @param string $userEmail User email
     * @param string $cardId Card ID
     *
     * @return array API response
     * @throws APIException
     */
    public function visaUnfreezeCard(string $userEmail, string $cardId): array
    {
        return $this->post('visaunblockcard', [
            'useremail' => $userEmail,
            'cardid' => $cardId,
        ]);
    }

    /**
     * Fund a Visa card
     *
     * @param string $userEmail User email
     * @param string $cardId Card ID
     * @param string $amount Amount (minimum $10.00)
     *
     * @return array API response
     * @throws APIException
     */
    public function visaFundCard(string $userEmail, string $cardId, string $amount): array
    {
        return $this->post('visafundcard', [
            'useremail' => $userEmail,
            'cardid' => $cardId,
            'amount' => $amount,
        ]);
    }

    // ========================
    // DIGITAL WALLET CARDS
    // ========================

    /**
     * Create a new Digital Wallet card
     *
     * @param string $userEmail User email
     * @param string $firstName First name
     * @param string $lastName Last name
     * @param string $dateOfBirth Date of birth (YYYY-MM-DD)
     * @param string $address1 Address line 1
     * @param string $postalCode Postal code
     * @param string $city City
     * @param string $country Country code (e.g., 'GB', 'US')
     * @param string $state State/Province
     * @param string $countryCode Country calling code (e.g., '44', '1')
     * @param string $phone Phone number
     *
     * @return array API response
     * @throws APIException
     */
    public function digitalCreateVirtualCard(
        string $userEmail,
        string $firstName,
        string $lastName,
        string $dateOfBirth,
        string $address1,
        string $postalCode,
        string $city,
        string $country,
        string $state,
        string $countryCode,
        string $phone
    ): array {
        return $this->post('digitalnewvirtualcard', [
            'useremail' => $userEmail,
            'firstname' => $firstName,
            'lastname' => $lastName,
            'dob' => $dateOfBirth,
            'address1' => $address1,
            'postalcode' => $postalCode,
            'city' => $city,
            'country' => $country,
            'state' => $state,
            'countrycode' => $countryCode,
            'phone' => $phone,
        ]);
    }

    /**
     * Get all Digital Wallet cards for a user
     *
     * @param string $userEmail User email
     *
     * @return array API response
     * @throws APIException
     */
    public function digitalGetAllCards(string $userEmail): array
    {
        return $this->post('digitalgetallvirtualcards', [
            'useremail' => $userEmail,
        ]);
    }

    /**
     * Get specific Digital Wallet card details
     *
     * @param string $userEmail User email
     * @param string $cardId Card ID
     *
     * @return array API response
     * @throws APIException
     */
    public function digitalGetCard(string $userEmail, string $cardId): array
    {
        return $this->post('digitalgetvirtualcard', [
            'useremail' => $userEmail,
            'cardid' => $cardId,
        ]);
    }


    /**
     * Fund a Digital Wallet card
     *
     * @param string $userEmail User email
     * @param string $cardId Card ID
     * @param string $amount Amount
     *
     * @return array API response
     * @throws APIException
     */
    public function digitalFundCard(string $userEmail, string $cardId, string $amount): array
    {
        return $this->post('digitalfundcard', [
            'useremail' => $userEmail,
            'cardid' => $cardId,
            'amount' => $amount,
        ]);
    }

    /**
     * Freeze a Digital Wallet card
     *
     * @param string $userEmail User email
     * @param string $cardId Card ID
     *
     * @return array API response
     * @throws APIException
     */
    public function digitalFreezeCard(string $userEmail, string $cardId): array
    {
        return $this->post('digitalblockcard', [
            'useremail' => $userEmail,
            'cardid' => $cardId,
        ]);
    }

    /**
     * Unfreeze a Digital Wallet card
     *
     * @param string $userEmail User email
     * @param string $cardId Card ID
     *
     * @return array API response
     * @throws APIException
     */
    public function digitalUnfreezeCard(string $userEmail, string $cardId): array
    {
        return $this->post('digitalunblockcard', [
            'useremail' => $userEmail,
            'cardid' => $cardId,
        ]);
    }

    /**
     * Check 3DS verification status
     *
     * @param string $userEmail User email
     *
     * @return array API response
     * @throws APIException
     */
    public function digitalCheck3DS(string $userEmail): array
    {
        return $this->post('checkwallet', [
            'useremail' => $userEmail,
        ]);
    }

    /**
     * Approve 3DS transaction
     *
     * @param string $userEmail User email
     * @param string $cardId Card ID
     * @param string $eventId Event ID from 3DS authorization
     *
     * @return array API response
     * @throws APIException
     */
    public function digitalApprove3DS(string $userEmail, string $cardId, string $eventId): array
    {
        return $this->post('approve3ds', [
            'useremail' => $userEmail,
            'cardid' => $cardId,
            'eventId' => $eventId,
        ]);
    }

    /**
     * Terminate a Digital Wallet card
     *
     * @param string $userEmail User email
     * @param string $cardId Card ID
     *
     * @return array API response
     * @throws APIException
     */
    public function digitalTerminateCard(string $userEmail, string $cardId): array
    {
        return $this->post('terminatedigitalcard', [
            'useremail' => $userEmail,
            'cardid' => $cardId,
        ]);
    }

    /**
     * Create an add-on card under the same balance
     *
     * @param string $userEmail User email
     * @param string $cardId Parent card ID
     *
     * @return array API response
     * @throws APIException
     */
    public function digitalCreateAddonCard(string $userEmail, string $cardId): array
    {
        return $this->post('createaddon', [
            'useremail' => $userEmail,
            'cardid' => $cardId,
        ]);
    }

    /**
     * Get Digital Wallet card loyalty points
     *
     * @param string $userEmail User email
     * @param string $cardId Card ID
     *
     * @return array API response
     * @throws APIException
     */
    public function digitalGetLoyaltyPoints(string $userEmail, string $cardId): array
    {
        return $this->post('digitalcardpoints', [
            'useremail' => $userEmail,
            'cardid' => $cardId,
        ]);
    }

    /**
     * Redeem Digital Wallet card loyalty points
     *
     * @param string $userEmail User email
     * @param string $cardId Card ID
     *
     * @return array API response
     * @throws APIException
     */
    public function digitalRedeemPoints(string $userEmail, string $cardId): array
    {
        return $this->post('redeempoints', [
            'useremail' => $userEmail,
            'cardid' => $cardId,
        ]);
    }

    // ========================
    // ADMINISTRATOR METHODS
    // ========================

    /**
     * Get wallet balance
     *
     * @return array API response
     * @throws APIException
     */
    public function getWalletBalance(): array
    {
        return $this->get('admin/balance');
    }

    /**
     * Get deposits
     *
     * @return array API response
     * @throws APIException
     */
    public function getDeposits(): array
    {
        return $this->get('admin/deposits');
    }

    /**
     * Get all transactions
     *
     * @return array API response
     * @throws APIException
     */
    public function getTransactions(): array
    {
        return $this->get('admin/transactions');
    }

    /**
     * Get all Visa cards
     *
     * @return array API response
     * @throws APIException
     */
    public function getAllVisaCards(): array
    {
        return $this->get('admin/visacards');
    }

    /**
     * Get all MasterCards
     *
     * @return array API response
     * @throws APIException
     */
    public function getAllMastercards(): array
    {
        return $this->get('admin/mastercards');
    }

    /**
     * Get all Digital Cards
     *
     * @return array API response
     * @throws APIException
     */
    public function getAllDigitalCards(): array
    {
        return $this->get('admin/digitalcards');
    }

    // ========================
    // UTILITY METHODS
    // ========================

    /**
     * Set public key
     *
     * @param string $publicKey Public API key
     *
     * @return self
     */
    public function setPublicKey(string $publicKey): self
    {
        $this->publicKey = $publicKey;
        return $this;
    }

    /**
     * Set secret key
     *
     * @param string $secretKey Secret API key
     *
     * @return self
     */
    public function setSecretKey(string $secretKey): self
    {
        $this->secretKey = $secretKey;
        return $this;
    }

    /**
     * Get public key
     *
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * Get secret key
     *
     * @return string
     */
    public function getSecretKey(): string
    {
        return $this->secretKey;
    }
}

