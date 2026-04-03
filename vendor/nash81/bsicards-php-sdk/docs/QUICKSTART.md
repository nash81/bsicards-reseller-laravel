# BSICARDS SDK - Quick Start (5 Minutes)

## 1. Installation (1 minute)

```bash
composer require nash81/bsicards-php-sdk
```

## 2. Configuration (1 minute)

Create `.env` file in your project root:

```env
BSICARDS_PUBLIC_KEY=your_public_key_here
BSICARDS_SECRET_KEY=your_secret_key_here
```

Get your keys from: https://www.bsigroup.tech/dashboard

## 3. Initialize Client (1 minute)

```php
<?php
require 'vendor/autoload.php';

use BSICards\BSICardsClient;
use BSICards\APIException;

// Create client (automatically loads from .env)
$client = new BSICardsClient();
```

## 4. Create a Card (1 minute)

### MasterCard

```php
try {
    $response = $client->mastercardCreateCard(
        'user@example.com',
        'John Doe',
        '1234'  // PIN
    );

    echo "Success: " . $response['message'];

} catch (APIException $e) {
    echo "Error: " . $e->getMessage();
}
```

### Visa Card

```php
try {
    $response = $client->visaCreateCard(
        'user@example.com',
        'John Doe',
        '12345678',  // National ID
        'https://example.com/id.pdf',
        'https://example.com/photo.jpg',
        '1990-01-15'
    );

    echo "Success: " . $response['message'];

} catch (APIException $e) {
    echo "Error: " . $e->getMessage();
}
```

### Digital Wallet Card

```php
try {
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

    echo "Success: " . $response['message'];

} catch (APIException $e) {
    echo "Error: " . $e->getMessage();
}
```

## 5. Get Card Details (1 minute)

```php
$cardDetails = $client->mastercardGetCard(
    'user@example.com',
    'card-id-here'
);

echo "Card Number: " . $cardDetails['data']['card_number'];
echo "Balance: " . $cardDetails['data']['available_balance'];
```

## That's It!

You're now ready to use the BSICARDS SDK. For more details, see:

- [Full README](README.md)
- [API Documentation](docs/API.md)
- [Code Examples](docs/EXAMPLES.md)
- [Installation Guide](docs/INSTALLATION.md)

## Need Help?

- Check [API.md](docs/API.md) for all available methods
- Review [EXAMPLES.md](docs/EXAMPLES.md) for code samples
- Email: cs@bsigroup.tech
- Website: https://www.bsigroup.tech

## Common Tasks

### Get All Cards

```php
$cards = $client->mastercardGetAllCards('user@example.com');
```

### Fund a Card

```php
$client->mastercardFundCard('user@example.com', 'card-id', '50.00');
```

### Freeze a Card

```php
$client->mastercardFreezeCard('user@example.com', 'card-id');
```

### View Transactions

```php
$txns = $client->mastercardGetTransactions('user@example.com', 'card-id');
```

### Change PIN

```php
$client->mastercardChangePin('user@example.com', 'card-id', '5678');
```

---

**Ready to dive deeper?** Check the full documentation in the `/docs` folder.

