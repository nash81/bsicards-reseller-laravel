# BSICARDS SDK - Code Examples

## Basic Setup

```php
<?php
require 'vendor/autoload.php';

use BSICards\BSICardsClient;
use BSICards\APIException;

// Initialize with environment variables
$client = new BSICardsClient();

// Or with explicit credentials
$client = new BSICardsClient('your_public_key', 'your_secret_key');
```

## MasterCard Examples

### Create a MasterCard

```php
try {
    $response = $client->mastercardCreateCard(
        'user@example.com',
        'John Doe',
        '1234'
    );

    if ($response['status'] === 'success') {
        echo "Card created successfully!";
    }
} catch (APIException $e) {
    echo "Error: " . $e->getMessage();
}
```

### Get All User Cards

```php
$cards = $client->mastercardGetAllCards('user@example.com');

if (!empty($cards['data'])) {
    foreach ($cards['data'] as $card) {
        echo "Card: " . $card['cardid'] . " - Balance: " . $card['available_balance'];
    }
}
```

### Get Card Details

```php
$cardDetails = $client->mastercardGetCard(
    'user@example.com',
    '2b4176d6c35649beb1d01b760e37c31c'
);

echo "Card Number: " . $cardDetails['data']['card_number'];
echo "Expiry: " . $cardDetails['data']['expiry_year'];
echo "Balance: " . $cardDetails['data']['available_balance'];
```

### View Transactions

```php
$transactions = $client->mastercardGetTransactions(
    'user@example.com',
    'card-id'
);

foreach ($transactions['data']['response']['data']['transactions'] as $txn) {
    echo $txn['transaction_date'] . ": " . $txn['amount'] . " " . $txn['currency'];
    echo " - " . $txn['description'];
}
```

### Fund a Card

```php
$response = $client->mastercardFundCard(
    'user@example.com',
    'card-id',
    '50.00'  // Minimum $10.00
);
```

### Freeze/Unfreeze Card

```php
// Freeze the card
$client->mastercardFreezeCard('user@example.com', 'card-id');

// Later, unfreeze it
$client->mastercardUnfreezeCard('user@example.com', 'card-id');
```

### Change PIN

```php
$response = $client->mastercardChangePin(
    'user@example.com',
    'card-id',
    '5678'  // New PIN
);
```

## Visa Card Examples

### Create a Visa Card

```php
$response = $client->visaCreateCard(
    'user@example.com',
    'John Doe',
    '12345678',  // National ID
    'https://example.com/id.pdf',
    'https://example.com/photo.jpg',
    '1990-01-15'  // DOB
);
```

### Get Pending Visa Cards

```php
$pending = $client->visaGetPendingCards('user@example.com');

echo "Pending cards: " . count($pending['data']);
foreach ($pending['data'] as $card) {
    echo $card['nameoncard'] . " - Status: " . $card['status'];
}
```

## Digital Wallet Examples

### Create Virtual Card

```php
$response = $client->digitalCreateVirtualCard(
    'user@example.com',
    'John',
    'Doe',
    '1990-01-15',
    '128 City Road',
    'EC1V 2NX',
    'London',
    'GB',
    'England',
    '44',
    '2071234567'
);
```

### Fund Virtual Card

```php
$response = $client->digitalFundCard(
    'user@example.com',
    'card-id-123',
    '50.00'
);
```

### Check 3DS Verification

```php
$response = $client->digitalCheck3DS('user@example.com');

if ($response['code'] == 200) {
    echo "3DS Status: " . $response['data']['status'];
}
```

### Approve 3DS Transaction

```php
$response = $client->digitalApprove3DS(
    'user@example.com',
    'card-id-123',
    'event-id-from-3ds-request'
);

echo "3DS Approval: " . $response['message'];
```

### Terminate Card

```php
$response = $client->digitalTerminateCard(
    'user@example.com',
    'card-id-123'
);

echo "Card Status: " . $response['message'];
```

### Create Add-on Card

```php
$response = $client->digitalCreateAddonCard(
    'user@example.com',
    'parent-card-id-123'
);

// Add-on cards share the same balance as parent card
// Charged $4.50 per card
echo "Add-on Card Created: " . $response['message'];
```

### Get and Redeem Loyalty Points

```php
// Get loyalty points balance
$points = $client->digitalGetLoyaltyPoints(
    'user@example.com',
    'card-id-123'
);

echo "Available Points: " . $points['data']['balance'];

// Redeem loyalty points
$redeem = $client->digitalRedeemPoints(
    'user@example.com',
    'card-id-123'
);

echo "Redemption Status: " . $redeem['message'];
```

## Laravel Examples

### Using Dependency Injection in Controllers

```php
namespace App\Http\Controllers;

use BSICards\BSICardsClient;
use BSICards\APIException;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function create(Request $request, BSICardsClient $client)
    {
        try {
            $response = $client->mastercardCreateCard(
                $request->input('email'),
                $request->input('name'),
                $request->input('pin')
            );

            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (APIException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getCards($email, BSICardsClient $client)
    {
        try {
            $cards = $client->mastercardGetAllCards($email);
            return response()->json($cards);
        } catch (APIException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
```

### Using a Service Class

```php
namespace App\Services;

use BSICards\BSICardsClient;
use BSICards\APIException;

class CardService
{
    private $client;

    public function __construct(BSICardsClient $client)
    {
        $this->client = $client;
    }

    public function createCard($email, $name, $pin)
    {
        try {
            return $this->client->mastercardCreateCard($email, $name, $pin);
        } catch (APIException $e) {
            logger()->error('Card creation failed', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getCardDetails($email, $cardId)
    {
        try {
            return $this->client->mastercardGetCard($email, $cardId);
        } catch (APIException $e) {
            logger()->error('Failed to fetch card details', [
                'card_id' => $cardId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
```

## Error Handling with Logging

```php
use BSICards\APIException;
use Monolog\Logger;
use Monolog\Handlers\StreamHandler;

$log = new Logger('bsicards');
$log->pushHandler(new StreamHandler('logs/bsicards.log'));

try {
    $response = $client->mastercardCreateCard(
        'user@example.com',
        'John Doe',
        '1234'
    );

    $log->info('Card created', ['response' => $response]);
} catch (APIException $e) {
    $log->error('Card creation failed', [
        'error' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
}
```

## Batch Operations

```php
$users = [
    ['email' => 'user1@example.com', 'name' => 'User One', 'pin' => '1111'],
    ['email' => 'user2@example.com', 'name' => 'User Two', 'pin' => '2222'],
    ['email' => 'user3@example.com', 'name' => 'User Three', 'pin' => '3333'],
];

$results = [];

foreach ($users as $user) {
    try {
        $response = $client->mastercardCreateCard(
            $user['email'],
            $user['name'],
            $user['pin']
        );

        $results[$user['email']] = [
            'success' => true,
            'message' => $response['message']
        ];
    } catch (APIException $e) {
        $results[$user['email']] = [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Log results
foreach ($results as $email => $result) {
    if ($result['success']) {
        echo "✓ $email: Card created\n";
    } else {
        echo "✗ $email: {$result['error']}\n";
    }
}
```

## Custom Configuration

```php
$client = new BSICardsClient(
    'your_public_key',
    'your_secret_key',
    [
        'timeout' => 60,           // 60 seconds
        'connect_timeout' => 15,   // 15 seconds for connection
    ]
);
```

## Switching Credentials

```php
$client = new BSICardsClient('key1', 'secret1');

// Use account 1
$cards1 = $client->mastercardGetAllCards('user1@example.com');

// Switch to account 2
$client->setPublicKey('key2');
$client->setSecretKey('secret2');

// Use account 2
$cards2 = $client->mastercardGetAllCards('user2@example.com');
```

## Administrator Operations

### Get Wallet Balance

```php
$response = $client->getWalletBalance();

if ($response['code'] == 200) {
    echo "Wallet Balance: " . $response['data']['balance'];
}
```

### Get All Deposits

```php
$response = $client->getDeposits();

foreach ($response['data'] as $deposit) {
    echo "Deposit: " . $deposit['amount'] . " on " . $deposit['created_at'];
}
```

### Get All Transactions

```php
$response = $client->getTransactions();

if ($response['code'] == 200) {
    echo "Total Transactions: " . count($response['data']);
}
```

### Get All Card Types

```php
// Get all Visa cards
$visaCards = $client->getAllVisaCards();
echo "Total Visa Cards: " . count($visaCards['data']);

// Get all MasterCards
$mastercards = $client->getAllMastercards();
echo "Total MasterCards: " . count($mastercards['data']);

// Get all Digital cards
$digitalCards = $client->getAllDigitalCards();
echo "Total Digital Cards: " . count($digitalCards['data']);
```
