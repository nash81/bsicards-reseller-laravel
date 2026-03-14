import '../config/app_config.dart';
import '../models/user.dart';
import 'api_service.dart';
import 'dart:io';
import 'package:flutter/foundation.dart';

class AuthService {
  static Future<Map<String, dynamic>> login(String email, String password) async {
    final data = await ApiService.post(
      AppConfig.loginEndpoint,
      body: {'email': email, 'password': password},
      auth: false,
    );
    if (data['token'] != null) {
      await ApiService.saveToken(data['token']);
    }
    return data;
  }

  static Future<Map<String, dynamic>> register({
    required String firstName,
    required String lastName,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? phone,
    String? country,
  }) async {
    final data = await ApiService.post(
      AppConfig.registerEndpoint,
      body: {
        'first_name': firstName,
        'last_name': lastName,
        'email': email,
        'password': password,
        'password_confirmation': passwordConfirmation,
        if (phone != null) 'phone': phone,
        if (country != null) 'country': country,
      },
      auth: false,
    );
    if (data['token'] != null) {
      await ApiService.saveToken(data['token']);
    }
    return data;
  }

  static Future<void> logout() async {
    try {
      await ApiService.post(AppConfig.logoutEndpoint);
    } catch (_) {}
    await ApiService.deleteToken();
  }

  static Future<User> getMe() async {
    final data = await ApiService.get(AppConfig.meEndpoint);
    return User.fromJson(data['user'] as Map<String, dynamic>);
  }

  static Future<void> changePassword({
    required String currentPassword,
    required String newPassword,
    required String confirmation,
  }) async {
    await ApiService.post(
      AppConfig.changePasswordEndpoint,
      body: {
        'current_password': currentPassword,
        'password': newPassword,
        'password_confirmation': confirmation,
      },
    );
  }

  static Future<bool> isLoggedIn() async {
    final token = await ApiService.getToken();
    return token != null && token.isNotEmpty;
  }

  static Future<Map<String, dynamic>> updateProfile({
    required Map<String, String> fields,
    Map<String, File>? files,
  }) async {
    debugPrint('🔄 Sending profile update: fields=${fields.keys.toList()}, hasFiles=${files != null}');
    if (files != null) {
      debugPrint('   Files: ${files.keys.toList()}');
    }

    final response = await ApiService.postMultipart(
      AppConfig.updateProfileEndpoint,
      fields: fields,
      files: files,
    );

    debugPrint('📤 Profile update response: ${response['status']}');
    return response;
  }
}

