import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../config/app_theme.dart';
import '../config/app_colors.dart';

class ThemeProvider extends ChangeNotifier {
  static const _storageKey = 'app_theme';

  AppThemeName _themeName = AppThemeName.oceanTribe;

  AppThemeName get themeName => _themeName;

  ThemeData get themeData {
    switch (_themeName) {
      case AppThemeName.blackTiger:
        return AppTheme.blackTigerTheme;
      case AppThemeName.mysteriousElegance:
        return AppTheme.mysteriousEleganceTheme;
      case AppThemeName.oceanTribe:
        return AppTheme.oceanTribeTheme;
    }
  }

  AppColors get palette {
    switch (_themeName) {
      case AppThemeName.blackTiger:
        return AppColors.blackTiger;
      case AppThemeName.mysteriousElegance:
        return AppColors.mysteriousElegance;
      case AppThemeName.oceanTribe:
        return AppColors.oceanTribe;
    }
  }

  /// Human-readable label for the current theme.
  String get themeLabel {
    switch (_themeName) {
      case AppThemeName.blackTiger:
        return 'Black Tiger';
      case AppThemeName.mysteriousElegance:
        return 'Mysterious Elegance';
      case AppThemeName.oceanTribe:
        return 'Ocean Tribe';
    }
  }

  Future<void> load() async {
    final prefs = await SharedPreferences.getInstance();
    final saved = prefs.getString(_storageKey);
    if (saved == AppThemeName.blackTiger.name) {
      _themeName = AppThemeName.blackTiger;
    } else if (saved == AppThemeName.mysteriousElegance.name) {
      _themeName = AppThemeName.mysteriousElegance;
    } else {
      _themeName = AppThemeName.oceanTribe;
    }
  }

  Future<void> setTheme(AppThemeName name) async {
    if (_themeName == name) return;
    _themeName = name;
    notifyListeners();
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_storageKey, name.name);
  }
}

