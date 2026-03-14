import 'package:flutter/services.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:local_auth/local_auth.dart';

class BiometricLoginService {
  static const _storage = FlutterSecureStorage();
  static const String _enabledKey = 'biometric_enabled';
  static const String _emailKey = 'biometric_email';
  static const String _passwordKey = 'biometric_password';

  final LocalAuthentication _localAuth = LocalAuthentication();

  Future<bool> isEnabled() async {
    final flag = await _storage.read(key: _enabledKey);
    return flag == '1';
  }

  Future<bool> hasSavedCredentials() async {
    final email = await _storage.read(key: _emailKey);
    final password = await _storage.read(key: _passwordKey);
    return (email ?? '').isNotEmpty && (password ?? '').isNotEmpty;
  }

  Future<bool> canUseBiometric() async {
    try {
      final canCheck = await _localAuth.canCheckBiometrics;
      final supported = await _localAuth.isDeviceSupported();
      return canCheck && supported;
    } on PlatformException {
      return false;
    }
  }

  Future<void> enableWithCredentials({
    required String email,
    required String password,
  }) async {
    await _storage.write(key: _emailKey, value: email);
    await _storage.write(key: _passwordKey, value: password);
    await _storage.write(key: _enabledKey, value: '1');
  }

  Future<void> disableAndClear() async {
    await _storage.delete(key: _enabledKey);
    await _storage.delete(key: _emailKey);
    await _storage.delete(key: _passwordKey);
  }

  Future<({String email, String password})?> readCredentials() async {
    final email = await _storage.read(key: _emailKey);
    final password = await _storage.read(key: _passwordKey);
    if ((email ?? '').isEmpty || (password ?? '').isEmpty) return null;
    return (email: email!, password: password!);
  }

  Future<bool> authenticate() async {
    try {
      return await _localAuth.authenticate(
        localizedReason: 'Authenticate to sign in to BSI Cards',
        options: const AuthenticationOptions(
          biometricOnly: true,
          stickyAuth: true,
        ),
      );
    } on PlatformException {
      return false;
    }
  }
}

