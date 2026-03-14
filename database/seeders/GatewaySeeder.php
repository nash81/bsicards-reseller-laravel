<?php

namespace Database\Seeders;

use App\Models\Gateway;
use DB;
use Illuminate\Database\Seeder;

class GatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Gateway::truncate();

        $paypalCredentials = [
            'client_id' => '',
            'client_secret' => '',
            'app_id' => 'APP-80W284485P519543T',
            'mode' => 'sandbox',
        ];
        $paypalCurrency = [
            'AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'INR', 'ILS', 'JPY',
            'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'RUB', 'SGD', 'SEK', 'CHF', 'THB', 'USD',
        ];

        // ======================= stripe =========================

        $stripeCredentials = [
            'stripe_key' => 'pk_test_replace_me',
            'stripe_secret' => 'sk_test_replace_me',
        ];

        $stripeCurrency = [
            'USD', 'AUD', 'BRL', 'CAD', 'CHF', 'DKK', 'EUR', 'GBP', 'HKD', 'INR', 'JPY', 'MXN', 'MYR', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD',
        ];

        // ===================== mollie ============================

        $mollieCredentials = [
            'api_key' => 'replace_me',
        ];

        $mollieCurrency = [
            'USD', 'EUR',
        ];

        // ===================== Perfect Money ============================

        $perfectmoneyCredentials = [
            'PM_ACCOUNTID' => '',
            'PM_PASSPHRASE' => '',
            'PM_MARCHANTID' => '',
            'PM_MARCHANT_NAME' => 'tdevs',
        ];

        $perfectmoneyCurrency = [
            'USD', 'EUR',
        ];

        // ===================== coinbase ============================

        $coinbaseCredentials = [
            'api_key' => 'replace_me',
            'api_version' => '2018-03-22',
            'api_secret' => 'replace_me',
        ];

        $coinbaseCurrency = [
            'USD', 'EUR',
        ];

        // ===================== paystack ============================

        $paystackCredentials = [
            'public_key' => 'pk_test_replace_me',
            'secret_key' => 'sk_test_replace_me',
            'merchant_email' => 'change-me@example.com',
        ];

        $paystackCurrency = [
            'GHS',
        ];

        // ===================== voguepay ============================

        $voguepayCredentials = [
            'merchant_id' => 'replace_me',
        ];

        $voguepayCurrency = [
            'NGN',
        ];

        // ================================== data insert ================

        DB::table('gateways')
            ->insert(
                [
                    [
                        'gateway_code' => 'paypal',
                        'name' => 'Paypal',
                        'logo' => 'global/gateway/paypal.png',
                        'type' => 'auto',
                        'charge' => 0,
                        'charge_type' => 'fixed',
                        'minimum_deposit' => 0,
                        'maximum_deposit' => 0,
                        'rate' => 1,
                        'status' => true,
                        'credentials' => json_encode($paypalCredentials),
                        'supported_currencies' => json_encode($paypalCurrency),
                        'currency' => 'USD',
                        'currency_symbol' => '$',
                    ],
                    [
                        'gateway_code' => 'stripe',
                        'name' => 'Stripe',
                        'logo' => 'global/gateway/stripe.png',
                        'type' => 'auto',
                        'charge' => 0,
                        'charge_type' => 'fixed',
                        'minimum_deposit' => 0,
                        'maximum_deposit' => 0,
                        'rate' => 1,
                        'status' => true,
                        'credentials' => json_encode($stripeCredentials),
                        'supported_currencies' => json_encode($stripeCurrency),
                        'currency' => 'USD',
                        'currency_symbol' => '$',
                    ],
                    [
                        'gateway_code' => 'mollie',
                        'name' => 'Mollie',
                        'logo' => 'global/gateway/mollie.png',
                        'type' => 'auto',
                        'charge' => 0,
                        'charge_type' => 'fixed',
                        'minimum_deposit' => 0,
                        'maximum_deposit' => 0,
                        'rate' => 1,
                        'status' => true,
                        'credentials' => json_encode($mollieCredentials),
                        'supported_currencies' => json_encode($mollieCurrency),
                        'currency' => 'USD',
                        'currency_symbol' => '$',
                    ],
                    [
                        'gateway_code' => 'perfectmoney',
                        'name' => 'Perfect Money',
                        'logo' => 'global/gateway/perfectmoney.png',
                        'type' => 'auto',
                        'charge' => 0,
                        'charge_type' => 'fixed',
                        'minimum_deposit' => 0,
                        'maximum_deposit' => 0,
                        'rate' => 1,
                        'status' => true,
                        'credentials' => json_encode($perfectmoneyCredentials),
                        'supported_currencies' => json_encode($perfectmoneyCurrency),
                        'currency' => 'USD',
                        'currency_symbol' => '$',
                    ],
                    [
                        'gateway_code' => 'coinbase',
                        'name' => 'Coinbase',
                        'logo' => 'global/gateway/coinbase.png',
                        'type' => 'auto',
                        'charge' => 0,
                        'charge_type' => 'fixed',
                        'minimum_deposit' => 0,
                        'maximum_deposit' => 0,
                        'rate' => 1,
                        'status' => true,
                        'credentials' => json_encode($perfectmoneyCredentials),
                        'supported_currencies' => json_encode($perfectmoneyCurrency),
                        'currency' => 'USD',
                        'currency_symbol' => '$',
                    ],
                    [
                        'gateway_code' => 'paystack',
                        'name' => 'Paystack',
                        'logo' => 'global/gateway/paystack.png',
                        'type' => 'auto',
                        'charge' => 0,
                        'charge_type' => 'fixed',
                        'minimum_deposit' => 0,
                        'maximum_deposit' => 0,
                        'rate' => 1,
                        'status' => true,
                        'credentials' => json_encode($paystackCredentials),
                        'supported_currencies' => json_encode($paystackCurrency),
                        'currency' => 'USD',
                        'currency_symbol' => '$',
                    ],
                ]
            );
    }
}
