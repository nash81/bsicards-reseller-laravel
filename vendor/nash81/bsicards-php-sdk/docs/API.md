# BSICARDS SDK - API Documentation

Base URL: `API_ENDPOINT` (example: `https://cards.bsigroup.tech/api/merchant`)

All endpoints require authentication headers:
- `publickey`: Your public API key
- `secretkey`: Your secret API key

## MasterCard API

### Create MasterCard

```php
$response = $client->mastercardCreateCard(
    string $userEmail,
    string $nameOnCard,
    string $pin
): array
```

**Parameters:**
- `$userEmail` (string): User's email address
- `$nameOnCard` (string): Name to display on card
- `$pin` (string): 4-digit PIN

**Response:**
```json
{
    "code": 200,
    "status": "success",
    "message": "Card Request Received"
}
```

---

### Get All MasterCards

```php
$response = $client->mastercardGetAllCards(string $userEmail): array
```

**Parameters:**
- `$userEmail` (string): User's email address

---

### Get Pending MasterCards

```php
$response = $client->mastercardGetPendingCards(string $userEmail): array
```

Returns MasterCards that are still being processed.

---

### Get Specific MasterCard

```php
$response = $client->mastercardGetCard(
    string $userEmail,
    string $cardId
): array
```

**Response includes:**
- Card number
- Expiry date
- CVV
- Available balance
- Billing address
- Card status

---

### Get Card Transactions

```php
$response = $client->mastercardGetTransactions(
    string $userEmail,
    string $cardId
): array
```

Returns transaction history with:
- Transaction amount
- Date and time
- Type (credit/debit)
- Status
- Merchant information

---

### Change Card PIN

```php
$response = $client->mastercardChangePin(
    string $userEmail,
    string $cardId,
    string $newPin
): array
```

**Parameters:**
- `$newPin` (string): New 4-digit PIN

---

### Freeze Card

```php
$response = $client->mastercardFreezeCard(
    string $userEmail,
    string $cardId
): array
```

Temporarily blocks the card. Can be unfrozen later.

---

### Unfreeze Card

```php
$response = $client->mastercardUnfreezeCard(
    string $userEmail,
    string $cardId
): array
```

Reactivates a frozen card.

---

### Fund Card

```php
$response = $client->mastercardFundCard(
    string $userEmail,
    string $cardId,
    string $amount
): array
```

**Parameters:**
- `$amount` (string): Amount to fund (minimum $10.00)

**Example:**
```php
$response = $client->mastercardFundCard(
    'user@example.com',
    'card-123',
    '50.00'
);
```

---

## Visa API

### Create Visa Card

```php
$response = $client->visaCreateCard(
    string $userEmail,
    string $nameOnCard,
    string $nationalIdNumber,
    string $nationalIdImage,
    string $userPhoto,
    string $dateOfBirth
): array
```

**Parameters:**
- `$userEmail` (string): User's email
- `$nameOnCard` (string): Name on card
- `$nationalIdNumber` (string): National ID number
- `$nationalIdImage` (string): URL to national ID image
- `$userPhoto` (string): URL to user photo
- `$dateOfBirth` (string): Date in YYYY-MM-DD format

**Example:**
```php
$response = $client->visaCreateCard(
    'user@example.com',
    'John Doe',
    '12345678',
    'https://example.com/id.pdf',
    'https://example.com/photo.jpg',
    '1990-01-15'
);
```

---

### Get All Visa Cards

```php
$response = $client->visaGetAllCards(string $userEmail): array
```

---

### Get Pending Visa Cards

```php
$response = $client->visaGetPendingCards(string $userEmail): array
```

---

### Get Specific Visa Card

```php
$response = $client->visaGetCard(
    string $userEmail,
    string $cardId
): array
```

---

## Administrator Operations

Admin methods retrieve account-wide data without requiring user email.

### Get Wallet Balance

```php
$response = $client->getWalletBalance(): array
```

---

### Get Deposits

```php
$response = $client->getDeposits(): array
```

---

### Get Transactions

```php
$response = $client->getTransactions(): array
```

---

### Get All Visa Cards

```php
$response = $client->getAllVisaCards(): array
```

---

### Get All MasterCards

```php
$response = $client->getAllMastercards(): array
```

---

### Get All Digital Cards

```php
$response = $client->getAllDigitalCards(): array
```

---

## Error Handling

```php
use BSICards\APIException;
    string $userEmail,
    string $cardId
): array
```

---

### Freeze Visa Card

```php
$response = $client->visaFreezeCard(
    string $userEmail,
    string $cardId
): array
```

---

### Unfreeze Visa Card

```php
$response = $client->visaUnfreezeCard(
    string $userEmail,
    string $cardId
): array
```

---

### Fund Visa Card

```php
$response = $client->visaFundCard(
    string $userEmail,
    string $cardId,
    string $amount
): array
```

---

## Digital Wallet API

### Create Virtual Card

```php
$response = $client->digitalCreateVirtualCard(
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
): array
```

**Parameters:**
- `$country` (string): 2-letter country code (e.g., 'GB', 'US')
- `$countryCode` (string): Phone country code (e.g., '44' for UK)
- `$dateOfBirth` (string): YYYY-MM-DD format

**Example:**
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

---

### Get All Digital Cards

```php
$response = $client->digitalGetAllCards(string $userEmail): array
```

---

### Get Specific Digital Card

```php
$response = $client->digitalGetCard(
    string $userEmail,
    string $cardId
): array
```

---

### Fund Digital Card

```php
$response = $client->digitalFundCard(
    string $userEmail,
    string $cardId,
    string $amount
): array
```

---

### Freeze Digital Card

```php
$response = $client->digitalFreezeCard(
    string $userEmail,
    string $cardId
): array
```

---

### Unfreeze Digital Card

```php
$response = $client->digitalUnfreezeCard(
    string $userEmail,
    string $cardId
): array
```

---

### Check 3DS Verification

```php
$response = $client->digitalCheck3DS(
    string $userEmail
): array
```

---

### Approve 3DS Transaction

```php
$response = $client->digitalApprove3DS(
    string $userEmail,
    string $cardId,
    string $eventId
): array
```

**Parameters:**
- `$eventId` (string): Event ID from 3DS authorization request

---

### Terminate Digital Card

```php
$response = $client->digitalTerminateCard(
    string $userEmail,
    string $cardId
): array
```

---

### Create Add-on Card

```php
$response = $client->digitalCreateAddonCard(
    string $userEmail,
    string $cardId
): array
```

**Note:** Add-on cards are charged $4.50 per card and share the same balance as the parent card.

---

### Get Loyalty Points

```php
$response = $client->digitalGetLoyaltyPoints(
    string $userEmail,
    string $cardId
): array
```

---

### Redeem Loyalty Points

```php
$response = $client->digitalRedeemPoints(
    string $userEmail,
    string $cardId
): array
```

---

## Error Handling

All methods throw `BSICards\APIException` on error:

```php
use BSICards\APIException;

try {
    $response = $client->mastercardCreateCard(...);
} catch (APIException $e) {
    echo "Error: " . $e->getMessage();
    echo "Code: " . $e->getCode();
}
```

## HTTP Status Codes

- `200` - Success
- `400` - Bad Request (invalid parameters)
- `401` - Unauthorized (invalid credentials)
- `403` - Forbidden (access denied)
- `429` - Too Many Requests (rate limited)
- `500` - Server Error

---

## Rate Limiting

The API has rate limits. If exceeded, you receive a `429` response. Implement exponential backoff for retries.

---

## Data Types

- Amounts are strings (e.g., "50.00")
- Dates are YYYY-MM-DD format
- IDs are strings (UUIDs or hex)
- Emails are standard email format

