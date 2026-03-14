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
