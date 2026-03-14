import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../l10n/app_localizations.dart';

class LocaleProvider extends ChangeNotifier {
  static const _storageKey = 'app_locale';

  Locale _locale = AppLocalizations.fallbackLocale;

  Locale get locale => _locale;

  Future<void> load() async {
    final prefs = await SharedPreferences.getInstance();
    final savedCode = prefs.getString(_storageKey);
    if (savedCode == null || savedCode.trim().isEmpty) return;

    final matched = AppLocalizations.supportedLocales.firstWhere(
      (item) => item.languageCode == savedCode,
      orElse: () => AppLocalizations.fallbackLocale,
    );

    if (matched.languageCode != _locale.languageCode) {
      _locale = matched;
      notifyListeners();
    }
  }

  Future<void> setLocale(Locale locale) async {
    if (_locale.languageCode == locale.languageCode) return;

    _locale = locale;
    notifyListeners();

    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_storageKey, locale.languageCode);
  }
}

