import 'package:flutter/material.dart';

// ── Semantic colours (same in every theme) ───────────────────────────────────
abstract final class AppSemanticColors {
  static const Color success = Color(0xFF4CAF50);
  static const Color error   = Color(0xFFE53935);
  static const Color warning = Color(0xFFFF9800);
  static const Color income  = Color(0xFF43A047);
  static const Color expense = Color(0xFFE53935);
}

// ── Per-theme colour palette ─────────────────────────────────────────────────
class AppColors extends ThemeExtension<AppColors> {
  final Color primary;
  final Color primaryDark;
  final Color bgDark;
  final Color bgCard;
  final Color surface;
  final Color surfaceLight;
  final Color textPrimary;
  final Color textSecondary;
  final Color divider;

  const AppColors({
    required this.primary,
    required this.primaryDark,
    required this.bgDark,
    required this.bgCard,
    required this.surface,
    required this.surfaceLight,
    required this.textPrimary,
    required this.textSecondary,
    required this.divider,
  });

  // ── Ocean Tribe (Telegram-inspired, cool blue) ────────────────────────────
  static const AppColors oceanTribe = AppColors(
    primary:       Color(0xFF2AABEE),
    primaryDark:   Color(0xFF229ED9),
    bgDark:        Color(0xFF17212B),
    bgCard:        Color(0xFF232E3C),
    surface:       Color(0xFF1C2733),
    surfaceLight:  Color(0xFF293543),
    textPrimary:   Color(0xFFFFFFFF),
    textSecondary: Color(0xFF8B9DAF),
    divider:       Color(0xFF293543),
  );

  // ── Black Tiger (Binance-inspired, dark + gold) ───────────────────────────
  static const AppColors blackTiger = AppColors(
    primary:       Color(0xFFF0B90B),
    primaryDark:   Color(0xFFD4A309),
    bgDark:        Color(0xFF0B0E11),
    bgCard:        Color(0xFF1E2026),
    surface:       Color(0xFF181A20),
    surfaceLight:  Color(0xFF2B2F36),
    textPrimary:   Color(0xFFEAECEF),
    textSecondary: Color(0xFF848E9C),
    divider:       Color(0xFF2B2F36),
  );

  // ── Mysterious Elegance (light, indigo accent) ────────────────────────────
  static const AppColors mysteriousElegance = AppColors(
    primary:       Color(0xFF6366F1),
    primaryDark:   Color(0xFF4F46E5),
    bgDark:        Color(0xFFFFFFFF),
    bgCard:        Color(0xFFF9FAFB),
    surface:       Color(0xFFF3F4F6),
    surfaceLight:  Color(0xFFE5E7EB),
    textPrimary:   Color(0xFF111827),
    textSecondary: Color(0xFF374151),
    divider:       Color(0xFFE5E7EB),
  );

  @override
  AppColors copyWith({
    Color? primary,
    Color? primaryDark,
    Color? bgDark,
    Color? bgCard,
    Color? surface,
    Color? surfaceLight,
    Color? textPrimary,
    Color? textSecondary,
    Color? divider,
  }) {
    return AppColors(
      primary:       primary       ?? this.primary,
      primaryDark:   primaryDark   ?? this.primaryDark,
      bgDark:        bgDark        ?? this.bgDark,
      bgCard:        bgCard        ?? this.bgCard,
      surface:       surface       ?? this.surface,
      surfaceLight:  surfaceLight  ?? this.surfaceLight,
      textPrimary:   textPrimary   ?? this.textPrimary,
      textSecondary: textSecondary ?? this.textSecondary,
      divider:       divider       ?? this.divider,
    );
  }

  @override
  AppColors lerp(AppColors? other, double t) {
    if (other == null) return this;
    return AppColors(
      primary:       Color.lerp(primary,       other.primary,       t)!,
      primaryDark:   Color.lerp(primaryDark,   other.primaryDark,   t)!,
      bgDark:        Color.lerp(bgDark,        other.bgDark,        t)!,
      bgCard:        Color.lerp(bgCard,        other.bgCard,        t)!,
      surface:       Color.lerp(surface,       other.surface,       t)!,
      surfaceLight:  Color.lerp(surfaceLight,  other.surfaceLight,  t)!,
      textPrimary:   Color.lerp(textPrimary,   other.textPrimary,   t)!,
      textSecondary: Color.lerp(textSecondary, other.textSecondary, t)!,
      divider:       Color.lerp(divider,       other.divider,       t)!,
    );
  }
}

// ── Convenience extension on BuildContext ────────────────────────────────────
extension AppColorsX on BuildContext {
  AppColors get colors =>
      Theme.of(this).extension<AppColors>() ?? AppColors.oceanTribe;
}

