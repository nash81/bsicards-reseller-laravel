# Installer

This folder contains a standalone web installer at `install/index.php`.

## What it does

- Prompts for:
  - Website URL (`APP_URL`)
  - Database name (`DB_DATABASE`)
  - Database username (`DB_USERNAME`)
  - Database password (`DB_PASSWORD`)
- Validates database connectivity before writing any settings.
- Updates the root `.env` file (creates `.env.install.bak` backup first).
- Imports `install/bsicards.sql` into the configured database.
- Shows on-screen errors for validation, connection, `.env` write, and SQL import failures.

## Usage

1. Open `https://your-domain/install/index.php` in your browser.
2. Fill in the fields and click **Install Now**.
3. Fix any errors shown on screen and retry.

## Notes

- The installer uses `DB_HOST` and `DB_PORT` already present in `.env` (defaults to `127.0.0.1:3306` if missing).
- If `storage/installed` already exists, a warning is shown because rerunning may overwrite data.
- Requires PHP `mysqli` extension enabled.

