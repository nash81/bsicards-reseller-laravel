import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'app_colors.dart';

enum AppThemeName { oceanTribe, blackTiger, mysteriousElegance }

class AppTheme {
  // ── Semantic colours (unchanged across themes) ─────────────────────────────
  static const Color success = AppSemanticColors.success;
  static const Color error   = AppSemanticColors.error;
  static const Color warning = AppSemanticColors.warning;
  static const Color income  = AppSemanticColors.income;
  static const Color expense = AppSemanticColors.expense;

  // ── Legacy static fallbacks (Ocean Tribe defaults) ─────────────────────────
  // These are used in auth/widget const constructors. Theme-aware screens should
  // use `context.colors.xxx` instead.
  static const Color primary       = Color(0xFF2AABEE);
  static const Color primaryDark   = Color(0xFF229ED9);
  static const Color bgDark        = Color(0xFF17212B);
  static const Color bgCard        = Color(0xFF232E3C);
  static const Color surface       = Color(0xFF1C2733);
  static const Color surfaceLight  = Color(0xFF293543);
  static const Color textPrimary   = Color(0xFFFFFFFF);
  static const Color textSecondary = Color(0xFF8B9DAF);
  static const Color divider       = Color(0xFF293543);

  // ── Build a ThemeData from any AppColors palette ────────────────────────────
  static ThemeData buildTheme(AppColors p, {Brightness brightness = Brightness.dark}) {
    final isDark = brightness == Brightness.dark;
    return ThemeData(
      useMaterial3: true,
      brightness: brightness,
      scaffoldBackgroundColor: p.bgDark,
      extensions: [p],
      colorScheme: isDark
          ? ColorScheme.dark(
              primary:   p.primary,
              onPrimary: p.textPrimary,
              secondary: p.primaryDark,
              surface:   p.surface,
              onSurface: p.textPrimary,
              error:     AppSemanticColors.error,
            )
          : ColorScheme.light(
              primary:   p.primary,
              onPrimary: Colors.white,
              secondary: p.primaryDark,
              surface:   p.surface,
              onSurface: p.textPrimary,
              error:     AppSemanticColors.error,
            ),
      textTheme: GoogleFonts.interTextTheme(
        (isDark ? ThemeData.dark() : ThemeData.light()).textTheme.apply(
          bodyColor:    p.textPrimary,
          displayColor: p.textPrimary,
        ),
      ),
      appBarTheme: AppBarTheme(
        backgroundColor: p.bgCard,
        elevation: 0,
        centerTitle: true,
        iconTheme: IconThemeData(color: p.textPrimary),
        titleTextStyle: TextStyle(
          color: p.textPrimary,
          fontSize: 18,
          fontWeight: FontWeight.w600,
          letterSpacing: 0.3,
        ),
      ),
      cardTheme: CardTheme(
        color: p.bgCard,
        elevation: 0,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        margin: EdgeInsets.zero,
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: p.surfaceLight,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: p.divider, width: 1),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: p.primary, width: 1.5),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppSemanticColors.error, width: 1),
        ),
        labelStyle: TextStyle(color: p.textSecondary),
        hintStyle: TextStyle(color: p.textSecondary),
        prefixIconColor: p.textSecondary,
        suffixIconColor: p.textSecondary,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: p.primary,
          foregroundColor: p.textPrimary,
          elevation: 0,
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: p.primary,
          side: BorderSide(color: p.primary),
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
        ),
      ),
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: p.primary,
          textStyle: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
        ),
      ),
      bottomNavigationBarTheme: BottomNavigationBarThemeData(
        backgroundColor: p.bgCard,
        selectedItemColor: p.primary,
        unselectedItemColor: p.textSecondary,
        type: BottomNavigationBarType.fixed,
        elevation: 0,
        showSelectedLabels: true,
        showUnselectedLabels: true,
      ),
      dividerTheme: DividerThemeData(color: p.divider, thickness: 1, space: 0),
      listTileTheme: ListTileThemeData(
        tileColor: p.bgCard,
        iconColor: p.textSecondary,
        textColor: p.textPrimary,
      ),
      chipTheme: ChipThemeData(
        backgroundColor: p.surfaceLight,
        selectedColor: p.primary.withValues(alpha: 0.2),
        labelStyle: TextStyle(color: p.textPrimary, fontSize: 12),
        side: BorderSide(color: p.divider),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      ),
      snackBarTheme: SnackBarThemeData(
        backgroundColor: p.bgCard,
        contentTextStyle: TextStyle(color: p.textPrimary),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  // ── Pre-built themes ────────────────────────────────────────────────────────
  static final ThemeData oceanTribeTheme = buildTheme(AppColors.oceanTribe);
  static final ThemeData blackTigerTheme = buildTheme(AppColors.blackTiger);
  static final ThemeData mysteriousEleganceTheme = buildTheme(
    AppColors.mysteriousElegance,
    brightness: Brightness.light,
  );

  // Legacy alias
  static ThemeData get darkTheme => oceanTribeTheme;
}
