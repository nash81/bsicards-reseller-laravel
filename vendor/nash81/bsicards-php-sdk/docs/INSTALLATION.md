# BSICARDS SDK - Installation Guide

## System Requirements

- **PHP**: Version 8.1 or higher
- **Composer**: Latest version
- **Curl**: For making HTTP requests (usually included)

## Installation Steps

### 1. Install via Composer (Recommended)

```bash
composer require nash81/bsicards-php-sdk
```

This will automatically:
- Download the SDK
- Install dependencies (GuzzleHTTP)
- Set up PSR-4 autoloading

### 2. Configure Environment Variables

Create or update your `.env` file:

```env
BSICARDS_PUBLIC_KEY=your_public_key_here
BSICARDS_SECRET_KEY=your_secret_key_here
```

Get your API keys from: https://www.bsigroup.tech

### 3. For Laravel Applications

The SDK includes a Laravel Service Provider that auto-registers.

#### Configuration (Optional)

Publish the configuration file:

```bash
php artisan vendor:publish --tag=bsicards-config
```

This creates `config/bsicards.php` where you can override settings.

### 4. For Non-Laravel Applications

The SDK works with any PHP framework. Just ensure:

1. Composer autoload is configured
2. Environment variables are loaded (via dotenv or similar)

## Verify Installation

Create a test file to verify everything works:

```php
<?php
require 'vendor/autoload.php';

use BSICards\BSICardsClient;

try {
    $client = new BSICardsClient();
    echo "✓ SDK initialized successfully!";
    echo "\nPublic Key: " . $client->getPublicKey();
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage();
}
```

Run it:

```bash
php test.php
```

## Troubleshooting

### "Class not found" Error

Make sure Composer's autoload is included:

```php
require 'vendor/autoload.php';
```

### "Keys are required" Error

Ensure your environment variables are set:

```bash
# Test if variables are set
echo $BSICARDS_PUBLIC_KEY
echo $BSICARDS_SECRET_KEY
```

### "Connection refused" Error

This usually means:
1. Internet connection issue
2. API endpoint is unreachable
3. Firewall blocking HTTPS

### SSL Certificate Issues

On Windows, you may need to download CA certificates:

```bash
# Option 1: Use Composer's CA bundle
# (Usually automatic with Guzzle)

# Option 2: Download cacert.pem manually
# Visit: https://curl.se/docs/caextract.html
```

## Next Steps

1. Read [README.md](../README.md) for API overview
2. Check [QUICKSTART.md](./QUICKSTART.md) for 5-minute setup
3. Review [EXAMPLES.md](./EXAMPLES.md) for code samples
4. See [API.md](./API.md) for endpoint documentation

## Getting Help

- Documentation: See `/docs` directory
- Email: cs@bsigroup.tech
- Website: https://www.bsigroup.tech

## Packagist Auto-Update

If Packagist says your package is not auto-updated, enable one-time setup:

1. In Packagist, open package `nash81/bsicards-php-sdk`
2. Use `Update` / `Auto Update` and connect GitHub repository
3. Confirm webhook is active in GitHub repository settings

Optional fallback (already added in this SDK):
- Workflow: `.github/workflows/packagist-update.yml`
- Add GitHub repository secret:
  - `PACKAGIST_TOKEN`
