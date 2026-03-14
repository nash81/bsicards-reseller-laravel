# BSI Cards - Installation Guide

This guide explains how to install the project and set up the MySQL database, including cPanel steps for creating the database, database user, and password.

## Prerequisites

- PHP >= 8.1 and web server configured for Laravel
- MySQL or MariaDB available
- Access to your hosting file manager/SSH
- cPanel access (for shared hosting)
- Project files uploaded to your hosting account

## 1) Clone or download the project

Clone from GitHub:

```bash
git clone https://github.com/nash81/bsicards-laravel-frontend.git
cd bsicards-laravel-frontend
```

Or download and extract the project files.

**Note:** All dependencies (vendor folder) are included, so no need to run `composer install` unless you want to update dependencies.

## 2) Upload the project

Upload this project to your hosting path (for example `public_html` or a subfolder).

Make sure these paths exist:

- Project root: contains `artisan`, `composer.json`, `install/`
- SQL file: `install/bsicards.sql`
- Installer page: `install/index.php`

## 3) Create MySQL database and user in cPanel
2. Open **MySQL Databases**.
3. Under **Create New Database**:
   - Enter a database name (example: `bsicards`)
   - Click **Create Database**
4. Under **MySQL Users** -> **Add New User**:
   - Enter username (example: `bsicards_user`)
   - Enter a strong password
   - Confirm password
   - Click **Create User**
5. Under **Add User To Database**:
   - Select the new user
   - Select the new database
   - Click **Add**
6. Grant privileges:
   - Check **ALL PRIVILEGES**
   - Click **Make Changes**

### Important cPanel naming note

cPanel usually prefixes database/user names with your cPanel account name.

Examples:

- Database may become `cpanelname_bsicards`
- User may become `cpanelname_bsicards_user`

Use the **full prefixed values** in installation settings.

## 4) Configure `.env`

If `.env` is missing, copy from `.env.example` first.

Set at least:

```env
APP_URL=https://your-domain.com
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_full_database_name
DB_USERNAME=your_full_database_user
DB_PASSWORD=your_database_password
```

> On many cPanel hosts, `DB_HOST` remains `localhost` or `127.0.0.1`. Use the value provided by your host.

## 5) Run the web installer

Open in browser:

- `https://your-domain.com/install/index.php`

Enter:

- Website URL
- Database name
- Database username
- Database password

The installer will:

- Validate database connection
- Update `.env` values
- Import `install/bsicards.sql`
- Show clear errors if connection/import fails

## 6) Finalize Laravel setup (if needed)

If you have shell access, run:

```bash
php artisan optimize:clear
php artisan storage:link
```

If your host uses cron, configure Laravel scheduler (optional but recommended):

```bash
* * * * * php /home/USERNAME/path-to-project/artisan schedule:run >> /dev/null 2>&1
```
Admin Login: https://your-domain.com/admin
username: cs@cards.bsigroup.tech Password: Abcd4321@

## 7) Install the MonCash gateway seed (optional)

If you want to install the default MonCash gateway record into the `gateways` table, run:

```bash
php artisan db:seed --class=Database\\Seeders\\MoncashGatewaySeeder
```

What this seeder adds or updates:

- Gateway code: `moncash`
- Name: `MonCash`
- Supported currency: `HTG`
- Credentials keys: `clientId`, `clientSecret`, `businessKey`, `mode`
- Default mode: `sandbox`

The seeder is idempotent, so you can run it again later and it will update the existing `moncash` gateway record instead of creating duplicates.

If you are on shared hosting without terminal access, run the command once anywhere you do have Artisan access before deploying the updated database, or seed it locally and migrate the resulting record.

## Troubleshooting

### Database connection failed

- Confirm `DB_HOST`, `DB_PORT`, DB name, username, and password
- Confirm user is assigned to the database
- Confirm **ALL PRIVILEGES** are granted
- Use full cPanel-prefixed database/user names

### SQL import failed

- Confirm `install/bsicards.sql` exists and is readable
- Ensure the DB user has create/alter/insert privileges
- Retry after recreating an empty database

### 500 error after setup

- Clear cache:

```bash
php artisan optimize:clear
```

- Check logs in `storage/logs/laravel.log`

## Security recommendation

After successful installation, restrict access to `install/index.php` (or remove/rename installer files) so it cannot be reused in production.

## Future roadmap

- Add on Cards for Digital Mastercards (Completed)
- Implementation of Laravel Livewire
- Convert Project to use laravel SDK for BSI Cards API
- Mobile App API (Completed – see below)

---

## Mobile App API

All mobile endpoints live under the prefix **`/api/v1/`**.

### Authentication

All protected endpoints require the header:
```
Authorization: Bearer <token>
```

---

### Auth Endpoints (Public)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/auth/login` | Login – returns `token` and user object |
| POST | `/api/v1/auth/register` | Register a new user – returns `token` |

**Login request body:**
```json
{ "email": "user@example.com", "password": "secret" }
```

**Register request body:**
```json
{
  "first_name": "John", "last_name": "Doe",
  "email": "user@example.com",
  "password": "secret", "password_confirmation": "secret",
  "phone": "+1234567890", "country": "US"
}
```

---

### Auth Endpoints (Protected)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/auth/logout` | Revoke current token |
| GET | `/api/v1/auth/me` | Current user details |
| POST | `/api/v1/auth/change-password` | Change password |

---

### Profile & Balance

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/profile` | Full profile + balance summary |
| POST | `/api/v1/profile/update` | Update profile fields (multipart/form-data for avatar) |
| GET | `/api/v1/profile/balance` | Balance and totals only |
| GET | `/api/v1/profile/recent-transactions` | Last 10 transactions |

---

### Transactions

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/transactions` | Paginated transaction list |
| GET | `/api/v1/transactions/deposits` | Deposits only |
| GET | `/api/v1/transactions/withdrawals` | Withdrawals only |
| GET | `/api/v1/transactions/{tnx}` | Single transaction detail |

**Query parameters for `/api/v1/transactions`:**
- `limit` – records per page (default: 15)
- `type` – filter by type: `deposit`, `withdraw`, `subtract`, etc.
- `from` / `to` – date range: `YYYY-MM-DD`
- `search` – search by `tnx` or `description`

---

### Deposit / Payment Gateways

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/deposit/gateways` | List active payment gateways |
| POST | `/api/v1/deposit/initiate` | Initiate a deposit |
| GET | `/api/v1/deposit/status/{tnx}` | Check deposit transaction status |
| POST | `/api/v1/deposit/manual-proof` | Upload proof for manual deposits |

**Initiate deposit request body:**
```json
{ "gateway_code": "moncash", "amount": 500 }
```

**Automatic gateway response** – open `redirect_url` in a WebView:
```json
{
  "status": true,
  "type": "auto",
  "tnx": "TRXABC123",
  "redirect_url": "https://moncashbutton.digicelgroup.com/...",
  "amount": 500,
  "currency": "HTG"
}
```
After the user completes payment in the WebView, poll `GET /api/v1/deposit/status/{tnx}` until `txn_status` is `success`.

**Manual gateway response** – show instructions to the user:
```json
{
  "status": true,
  "type": "manual",
  "tnx": "TRXABC123",
  "payment_details": "...",
  "field_options": [...]
}
```
Then call `POST /api/v1/deposit/manual-proof` with `tnx` and optional `proof` file.

---

### Virtual Cards – MasterCard

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/cards/master` | List all mastercards + pending |
| GET | `/api/v1/cards/master/{cardId}` | Card details + transactions |
| POST | `/api/v1/cards/master/load` | Load funds `{ cardid, amount }` |
| POST | `/api/v1/cards/master/{cardId}/block` | Block a card |
| POST | `/api/v1/cards/master/{cardId}/unblock` | Unblock a card |

---

### Virtual Cards – Visa

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/cards/visa` | List all Visa cards + pending |
| GET | `/api/v1/cards/visa/{cardId}` | Card details + transactions |
| POST | `/api/v1/cards/visa/load` | Load funds `{ cardid, amount }` |
| POST | `/api/v1/cards/visa/{cardId}/block` | Block a card |

---

### Virtual Cards – Digital Mastercard (GPay/Apple Pay)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/cards/digital` | List all digital cards |
| POST | `/api/v1/cards/digital/apply` | Apply for a new digital card |
| POST | `/api/v1/cards/digital/addon` | Apply for an addon card `{ cardid }` |
| GET | `/api/v1/cards/digital/{cardId}` | Card details + 3DS info |
| POST | `/api/v1/cards/digital/load` | Load funds `{ cardid, amount }` |
| POST | `/api/v1/cards/digital/{cardId}/block` | Block a card |
| GET | `/api/v1/cards/digital/{cardId}/check-3ds` | Check pending 3DS transaction |
| POST | `/api/v1/cards/digital/{cardId}/approve-3ds` | Approve 3DS `{ eventid }` |
| GET | `/api/v1/cards/digital/{cardId}/wallet-otp` | Get Google/Apple Pay OTP |

**Apply for digital card request body:**
```json
{
  "firstname": "John", "lastname": "Doe",
  "address": "123 Main St", "city": "Miami",
  "state": "FL", "country": "US",
  "zip": "33101", "dob": "1990-01-15"
}
```

---

### Error Response Format

All endpoints return consistent JSON:
```json
{
  "status": false,
  "message": "Human-readable error message",
  "errors": { "field": ["validation error"] }
}
```

### HTTP Status Codes

# BSI Cards Flutter App

Modern Flutter mobile client for the BSI Cards Laravel backend APIs.

## Features
- Telegram-inspired dark theme (`#2AABEE` accent)
- Smooth animations (`flutter_animate`)
- Auth flow (login/register/logout)
- Dashboard with balance + recent transactions
- Card modules (Digital, Mastercard, Visa)
- Deposit flow with gateway selection + WebView checkout
- Transaction history and profile screens

## Project Structure
- `lib/config/` app config + theme
- `lib/models/` API models
- `lib/services/` HTTP + business services
- `lib/providers/` state management (`provider`)
- `lib/screens/` app UI screens
- `lib/widgets/` reusable UI components

## Getting Started

1. **Set your server URL** – Open `lib/config/app_config.dart` and change `baseUrl` to point to your website:
   ```dart
   static const String baseUrl = 'https://your-domain.com/api/v1';
   ```
2. Run `flutter pub get` to install dependencies.
3. Launch the app with `flutter run`.

## Run Locally
```bash
flutter pub get
flutter analyze
flutter test
flutter run
```

## Build Release APK
```bash
flutter build apk --release
```

## Notes
- This app expects your Laravel API token auth (`auth:sanctum`) endpoints created under `/api/v1`.
- If you use self-signed HTTPS in local testing, configure platform network security accordingly.


| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 401 | Unauthenticated (invalid/missing token) |
| 403 | Forbidden (account suspended / feature disabled) |
| 404 | Not found |
| 422 | Validation error |
| 500 | Server / gateway error |

