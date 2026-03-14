import 'package:flutter/foundation.dart';
import 'dart:io';
import '../models/user.dart';
import '../models/transaction.dart';
import '../services/auth_service.dart';
import '../services/api_service.dart';
import '../services/biometric_login_service.dart';
import '../config/app_config.dart';

class AuthProvider extends ChangeNotifier {
  final BiometricLoginService _biometric = BiometricLoginService();
  User? _user;
  bool _isLoading = false;
  String? _error;
  bool _isAuthenticated = false;

  User? get user => _user;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get isAuthenticated => _isAuthenticated;

  Future<bool> checkAuth() async {
    _isLoading = true;
    notifyListeners();
    try {
      final loggedIn = await AuthService.isLoggedIn();
      if (loggedIn) {
        _user = await AuthService.getMe();
        _isAuthenticated = true;
      }
    } catch (_) {
      _isAuthenticated = false;
      await AuthService.logout();
    }
    _isLoading = false;
    notifyListeners();
    return _isAuthenticated;
  }

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      final data = await AuthService.login(email, password);
      _user = User.fromJson(data['user'] as Map<String, dynamic>);
      _isAuthenticated = true;
      _isLoading = false;
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _error = e.message;
      _isLoading = false;
      notifyListeners();
      return false;
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> tryBiometricLogin() async {
    final enabled = await _biometric.isEnabled();
    final hasCredentials = await _biometric.hasSavedCredentials();
    if (!enabled || !hasCredentials) return false;

    final canUse = await _biometric.canUseBiometric();
    if (!canUse) return false;

    final approved = await _biometric.authenticate();
    if (!approved) return false;

    final creds = await _biometric.readCredentials();
    if (creds == null) return false;

    return login(creds.email, creds.password);
  }

  Future<bool> canUseBiometricLogin() async {
    final enabled = await _biometric.isEnabled();
    final hasCredentials = await _biometric.hasSavedCredentials();
    final canUse = await _biometric.canUseBiometric();
    return enabled && hasCredentials && canUse;
  }

  Future<bool> hasBiometricCredentials() async {
    final enabled = await _biometric.isEnabled();
    final hasCredentials = await _biometric.hasSavedCredentials();
    return enabled && hasCredentials;
  }

  Future<bool> isBiometricEnabled() async {
    return _biometric.isEnabled();
  }

  Future<bool> isBiometricSupported() async {
    return _biometric.canUseBiometric();
  }

  Future<bool> authenticateForAppUnlock() async {
    final enabled = await _biometric.isEnabled();
    if (!enabled) return true;

    final supported = await _biometric.canUseBiometric();
    if (!supported) return false;

    return _biometric.authenticate();
  }

  Future<void> enableBiometricLogin({
    required String email,
    required String password,
  }) async {
    await _biometric.enableWithCredentials(email: email, password: password);
  }

  Future<void> disableBiometricLogin() async {
    await _biometric.disableAndClear();
  }

  Future<bool> register({
    required String firstName,
    required String lastName,
    required String email,
    required String password,
    required String confirmation,
    String? phone,
    String? country,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      final data = await AuthService.register(
        firstName: firstName,
        lastName: lastName,
        email: email,
        password: password,
        passwordConfirmation: confirmation,
        phone: phone,
        country: country,
      );
      _user = User.fromJson(data['user'] as Map<String, dynamic>);
      _isAuthenticated = true;
      _isLoading = false;
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _error = e.message;
      _isLoading = false;
      notifyListeners();
      return false;
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    await AuthService.logout();
    _user = null;
    _isAuthenticated = false;
    await _biometric.disableAndClear();
    notifyListeners();
  }

  Future<void> refreshUser() async {
    try {
      _user = await AuthService.getMe();
      notifyListeners();
    } catch (e) {
      debugPrint('⚠️ refreshUser failed: $e');
    }
  }

  /// Update profile fields/avatar, apply the returned user to state immediately.
  Future<void> updateProfile({
    required Map<String, String> fields,
    Map<String, File>? files,
  }) async {
    final data = await AuthService.updateProfile(fields: fields, files: files);
    // ProfileController::update returns the fresh user under data['data']
    final raw = data['data'];
    if (raw != null) {
      _user = User.fromJson(raw as Map<String, dynamic>);
    } else {
      // Fallback: re-fetch from /auth/me
      _user = await AuthService.getMe();
    }
    notifyListeners();
  }

  Future<Map<String, dynamic>> getBalance() async {
    final data = await ApiService.get(AppConfig.balanceEndpoint);
    return data['data'] as Map<String, dynamic>;
  }

  Future<List<Transaction>> getRecentTransactions() async {
    final data = await ApiService.get(AppConfig.recentTransactionsEndpoint);
    return (data['data'] as List? ?? [])
        .map((e) => Transaction.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}

