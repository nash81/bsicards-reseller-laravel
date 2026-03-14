import 'package:flutter/material.dart';
import '../config/app_theme.dart';

class AppSnackbar {
  static final messengerKey = GlobalKey<ScaffoldMessengerState>();

  static String? _lastMessage;
  static DateTime? _lastShownAt;

  static void showError(String message) {
    _show(
      message.trim(),
      backgroundColor: AppTheme.error,
    );
  }

  static void _show(String message, {required Color backgroundColor}) {
    if (message.isEmpty) return;

    final now = DateTime.now();
    final duplicate =
        _lastMessage == message &&
        _lastShownAt != null &&
        now.difference(_lastShownAt!) < const Duration(milliseconds: 800);
    if (duplicate) return;

    _lastMessage = message;
    _lastShownAt = now;

    final messenger = messengerKey.currentState;
    if (messenger == null) return;

    messenger
      ..hideCurrentSnackBar()
      ..showSnackBar(
        SnackBar(
          content: Text(message),
          backgroundColor: backgroundColor,
          behavior: SnackBarBehavior.floating,
        ),
      );
  }
}

