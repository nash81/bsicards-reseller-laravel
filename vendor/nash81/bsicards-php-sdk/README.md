# BSICARDS PHP SDK

A comprehensive PHP SDK for integrating with the BSICARDS Card Issuance API. Create and manage Mastercard, Visa, and Digital Wallet cards with ease.

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-brightgreen)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue)](LICENSE)
[![Composer](https://img.shields.io/badge/composer-available-brightgreen)](https://packagist.org/)

## Features

- ✅ **MasterCard Issuance** - Create and manage MasterCards
- ✅ **Visa Card Issuance** - Create and manage Visa Cards
- ✅ **Digital Wallet Cards** - Create and manage Digital Wallet cards
- ✅ **Card Management** - Freeze, unfreeze, change PIN, view transactions
- ✅ **Card Funding** - Fund cards with minimum $10.00
- ✅ **Transaction History** - View detailed transaction records
- ✅ **Laravel Support** - Built-in service provider and configuration
- ✅ **Environment Configuration** - Secure credential management via .env
- ✅ **Type Safe** - Full PHP type hints and PHPDoc documentation
- ✅ **Error Handling** - Custom exceptions for API errors

## Requirements

- PHP >= 8.1
- Composer
- GuzzleHTTP 7.0+

## Installation

### Via Composer

```bash
composer require nash81/bsicards-php-sdk
```

### Packagist Auto-Update Setup

If Packagist shows "This package is not auto-updated", set up one of the following:

1. GitHub Hook (recommended by Packagist)
   - Open your package on Packagist
   - Click `Update` / `Auto Update`
   - Connect your GitHub repository (`nash81/bsicards-php-sdk`)
   - Packagist will create/manage the webhook

2. GitHub Actions fallback (included in this repo)
   - Add this GitHub repository secret:
     - `PACKAGIST_TOKEN`
   - Workflow file: `.github/workflows/packagist-update.yml`
   - This workflow pings Packagist for `https://github.com/nash81/bsicards-php-sdk` on push/release

### Manual Setup

1. Clone the repository
2. Copy `src/` and `config/` directories to your project
3. Ensure Composer autoload is configured

## Configuration

### Environment Variables

Create a `.env` file in your project root:

```env
BSICARDS_PUBLIC_KEY=your_public_key_here
BSICARDS_SECRET_KEY=your_secret_key_here
API_ENDPOINT=https://cards.bsigroup.tech/api/merchant
```

### Laravel Setup

For Laravel projects, the service provider is auto-discovered. Add credentials to your `.env`:

```env
BSICARDS_PUBLIC_KEY=your_public_key
BSICARDS_SECRET_KEY=your_secret_key
```

Optional: Publish configuration file:

```bash
php artisan vendor:publish --tag=bsicards-config
```

## Quick Start

### Basic Usage

```php
use BSICards\BSICardsClient;
use BSICards\APIException;

// Initialize client (credentials from .env)
$client = new BSICardsClient();

try {
    // Create a MasterCard
    $response = $client->mastercardCreateCard(
        'user@example.com',
        'John Doe',
        '1234'
    );

    echo "Card created: " . $response['message'];
} catch (APIException $e) {
    echo "Error: " . $e->getMessage();
}
```

### Laravel Usage

```php
use BSICards\BSICardsClient;

class CardController extends Controller
{
    public function createCard(BSICardsClient $client)
    {
        $response = $client->mastercardCreateCard(
            request('email'),
            request('name'),
            request('pin')
        );

        return response()->json($response);
    }
}
```

## API Methods

### MasterCard Operations

```php
// Create a new MasterCard
$client->mastercardCreateCard($email, $name, $pin);

// Get all MasterCards
$client->mastercardGetAllCards($email);

// Get pending MasterCards
$client->mastercardGetPendingCards($email);

// Get specific card details
$client->mastercardGetCard($email, $cardId);

// Get card transactions
$client->mastercardGetTransactions($email, $cardId);

// Change card PIN
$client->mastercardChangePin($email, $cardId, $newPin);

// Freeze card
$client->mastercardFreezeCard($email, $cardId);

// Unfreeze card
$client->mastercardUnfreezeCard($email, $cardId);

// Fund card (minimum $10.00)
$client->mastercardFundCard($email, $cardId, '50.00');
```

### Visa Card Operations

```php
// Create a new Visa card
$client->visaCreateCard(
    $email,
    $name,
    $nationalIdNumber,
    $nationalIdImageUrl,
    $userPhotoUrl,
    '1990-01-15' // DOB
);

// Get all Visa cards
$client->visaGetAllCards($email);

// Get pending Visa cards
$client->visaGetPendingCards($email);

// Get specific card
$client->visaGetCard($email, $cardId);

// Get transactions
$client->visaGetTransactions($email, $cardId);

// Freeze/Unfreeze
$client->visaFreezeCard($email, $cardId);
$client->visaUnfreezeCard($email, $cardId);

// Fund card
$client->visaFundCard($email, $cardId, '50.00');
```

### Digital Wallet Operations

```php
// Create virtual card
$client->digitalCreateVirtualCard(
    $email,
    $firstName,
    $lastName,
    '1990-01-15',
    $address,
    $postalCode,
    $city,
    'GB', // Country code
    $state,
    '44', // Country phone code
    $phone
);

// Get all cards
$client->digitalGetAllCards($email);

// Get specific card
$client->digitalGetCard($email, $cardId);

// Fund card
$client->digitalFundCard($email, $cardId, $amount);

// Freeze/Unfreeze
$client->digitalFreezeCard($email, $cardId);
$client->digitalUnfreezeCard($email, $cardId);

// Check 3DS verification
$client->digitalCheck3DS($email);

// Approve 3DS transaction
$client->digitalApprove3DS($email, $cardId, $eventId);

// Terminate card
$client->digitalTerminateCard($email, $cardId);

// Create add-on card
$client->digitalCreateAddonCard($email, $cardId);

// Loyalty points
$client->digitalGetLoyaltyPoints($email, $cardId);
$client->digitalRedeemPoints($email, $cardId);
```

### Administrator Operations

```php
// Get wallet balance
$balance = $client->getWalletBalance();

// Get all deposits
$deposits = $client->getDeposits();

// Get all transactions
$transactions = $client->getTransactions();

// Get all Visa cards
$visaCards = $client->getAllVisaCards();

// Get all MasterCards
$mastercards = $client->getAllMastercards();

// Get all Digital cards
$digitalCards = $client->getAllDigitalCards();
```

## Error Handling

```php
use BSICards\APIException;

try {
    $response = $client->mastercardCreateCard(
        'user@example.com',
        'John Doe',
        '1234'
    );
} catch (APIException $e) {
    // Handle API error
    echo "API Error: " . $e->getMessage();
    echo "Code: " . $e->getCode();
}
```

## Response Format

All API responses follow this format:

```json
{
    "code": 200,
    "status": "success",
    "message": "Descriptive message",
    "data": {}
}
```

## Advanced Configuration

### Custom HTTP Settings

```php
$client = new BSICardsClient(
    'public_key',
    'secret_key',
    [
        'timeout' => 60,
        'connect_timeout' => 15,
    ]
);
```

### Switching Credentials

```php
$client->setPublicKey('new_key');
$client->setSecretKey('new_secret');

$publicKey = $client->getPublicKey();
$secretKey = $client->getSecretKey();
```

## Base URL

The SDK uses the `API_ENDPOINT` environment variable for the base URL.

```
API_ENDPOINT=https://cards.bsigroup.tech/api/merchant
```

## Best Practices

1. **Never hardcode credentials** - Always use environment variables
2. **Use try-catch blocks** - Always handle API exceptions
3. **Validate input** - Check email and other data before sending
4. **Log errors** - Keep records of API failures
5. **Implement retry logic** - For network failures
6. **Cache responses** - Where appropriate for performance

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## License

This SDK is released under the MIT License. See [LICENSE](LICENSE) for details.

## Support

For issues, questions, or support:

- Email: cs@bsigroup.tech
- Website: https://www.bsigroup.tech
- Documentation: See `/docs` directory

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

## Disclaimer

This SDK is provided as-is. Always test in a sandbox environment before production use.
