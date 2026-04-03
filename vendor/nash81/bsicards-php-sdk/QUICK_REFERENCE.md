# BSICARDS SDK - Quick Reference

## Installation
```bash
composer require nash81/bsicards-php-sdk
```

## Basic Setup
```php
require 'vendor/autoload.php';
use BSICards\BSICardsClient;

$client = new BSICardsClient();  # Loads from .env
```

## Environment Variables
```env
BSICARDS_PUBLIC_KEY=your_key
BSICARDS_SECRET_KEY=your_secret
```

---

## MasterCard Methods

| Method | Purpose |
|--------|---------|
| `mastercardCreateCard($email, $name, $pin)` | Create card |
| `mastercardGetAllCards($email)` | Get all cards |
| `mastercardGetCard($email, $cardId)` | Get card details |
| `mastercardGetTransactions($email, $cardId)` | View transactions |
| `mastercardChangePin($email, $cardId, $pin)` | Change PIN |
| `mastercardFreezeCard($email, $cardId)` | Freeze card |
| `mastercardUnfreezeCard($email, $cardId)` | Unfreeze card |
| `mastercardFundCard($email, $cardId, $amount)` | Fund card |

## Visa Card Methods

| Method | Purpose |
|--------|---------|
| `visaCreateCard($email, $name, $nationalId, $idUrl, $photoUrl, $dob)` | Create card |
| `visaGetAllCards($email)` | Get all cards |
| `visaGetCard($email, $cardId)` | Get card details |
| `visaGetTransactions($email, $cardId)` | View transactions |
| `visaFreezeCard($email, $cardId)` | Freeze card |
| `visaUnfreezeCard($email, $cardId)` | Unfreeze card |
| `visaFundCard($email, $cardId, $amount)` | Fund card |

## Digital Wallet Methods

| Method | Purpose |
|--------|---------|
| `digitalCreateVirtualCard(...)` | Create card |
| `digitalGetAllCards($email)` | Get all cards |
| `digitalGetCard($email, $cardId)` | Get card details |
| `digitalFundCard($email, $cardId, $amount)` | Fund card |
| `digitalFreezeCard($email, $cardId)` | Freeze card |
| `digitalUnfreezeCard($email, $cardId)` | Unfreeze card |
| `digitalCheck3DS($email)` | Check 3DS verification |
| `digitalApprove3DS($email, $cardId, $eventId)` | Approve 3DS transaction |
| `digitalTerminateCard($email, $cardId)` | Terminate card |
| `digitalCreateAddonCard($email, $cardId)` | Create add-on card |
| `digitalGetLoyaltyPoints($email, $cardId)` | Get loyalty points |
| `digitalRedeemPoints($email, $cardId)` | Redeem loyalty points |

## Administrator Methods

| Method | Purpose |
|--------|---------|
| `getWalletBalance()` | Get wallet balance |
| `getDeposits()` | Get all deposits |
| `getTransactions()` | Get all transactions |
| `getAllVisaCards()` | Get all Visa cards |
| `getAllMastercards()` | Get all MasterCards |
| `getAllDigitalCards()` | Get all Digital cards |

---

## Common Examples

### Create MasterCard
```php
$response = $client->mastercardCreateCard(
    'user@example.com',
    'John Doe',
    '1234'
);
```

### Get Card Details
```php
$card = $client->mastercardGetCard('user@example.com', 'card-id');
echo "Balance: " . $card['data']['available_balance'];
```

### Fund Card
```php
$client->mastercardFundCard('user@example.com', 'card-id', '50.00');
```

### View Transactions
```php
$txns = $client->mastercardGetTransactions('user@example.com', 'card-id');
foreach ($txns['data']['response']['data']['transactions'] as $txn) {
    echo $txn['amount'] . " " . $txn['currency'];
}
```

### Error Handling
```php
use BSICards\APIException;

try {
    $response = $client->mastercardCreateCard(...);
} catch (APIException $e) {
    echo "Error: " . $e->getMessage();
}
```

### Laravel Usage
```php
class CardController extends Controller
{
    public function create(BSICardsClient $client)
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

---

## Response Format

All responses follow this structure:
```json
{
    "code": 200,
    "status": "success",
    "message": "Card Request Received",
    "data": {}
}
```

---

## Credentials

**Base URL**: `API_ENDPOINT` (example: `https://cards.bsigroup.tech/api/merchant`)

**Headers Required**:
- `publickey`: Your public API key
- `secretkey`: Your secret API key

---

## Documentation Files

| File | Purpose |
|------|---------|
| `README.md` | Main overview |
| `docs/QUICKSTART.md` | 5-minute setup |
| `docs/INSTALLATION.md` | Install guide |
| `docs/API.md` | Complete API reference |
| `docs/EXAMPLES.md` | Code examples |
| `GITHUB_SETUP.md` | GitHub setup |

---

## Deployment Steps

1. Create GitHub repo: https://github.com/new
2. Push code: `git push -u origin main`
3. Create tag: `git tag -a v1.0.0`
4. Register: https://packagist.org -> Submit
5. Enable auto-update in Packagist (`Update` / `Auto Update`) for `nash81/bsicards-php-sdk`
6. Optional fallback: add GitHub secret `PACKAGIST_TOKEN` for `.github/workflows/packagist-update.yml`

---

## Utility Methods

```php
$client->setPublicKey('new_key');
$client->setSecretKey('new_secret');

$key = $client->getPublicKey();
$secret = $client->getSecretKey();
```

---

## Support

- Email: cs@bsigroup.tech
- Website: https://www.bsigroup.tech
- GitHub: https://github.com/nash81/bsicards-php-sdk

---

**Version**: 1.0.0
**License**: MIT
**PHP**: 8.1+
