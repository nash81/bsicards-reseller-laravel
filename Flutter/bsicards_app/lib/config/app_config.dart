class AppConfig {
  // ── Change this to your server URL ──────────────────────────────────

  static const String baseUrl = 'https://reseller.bsigroup.tech/api/v1';

  /// Replaces `localhost` / `127.0.0.1` in a URL returned by the server
  /// with the actual host from [baseUrl] so that avatars and other assets
  /// can be loaded from a physical device or emulator.
  static String fixUrl(String url) {
    final trimmed = url.trim();
    if (trimmed.isEmpty) return trimmed;

    try {
      final uri = Uri.parse(trimmed);
      var path = _normalizeAssetPath(uri.path);

      if (uri.host == 'localhost' || uri.host == '127.0.0.1') {
        final base = Uri.parse(baseUrl);
        final rewritten = Uri(
          scheme: base.scheme,
          host: base.host,
          port: base.hasPort ? base.port : null,
          path: path,
          query: uri.hasQuery ? uri.query : null,
        );
        return rewritten.toString();
      }

      // Keep absolute URLs as-is except for accidental duplicated assets segment.
      if (uri.hasScheme && uri.host.isNotEmpty && path != uri.path) {
        final normalized = uri.replace(path: path);
        return normalized.toString();
      }

      // Handle relative paths coming from API (e.g. /assets/images/default.png).
      if (!uri.hasScheme && trimmed.startsWith('/')) {
        final base = Uri.parse(baseUrl);
        final absolute = Uri(
          scheme: base.scheme,
          host: base.host,
          port: base.hasPort ? base.port : null,
          path: path,
          query: uri.hasQuery ? uri.query : null,
        );
        return absolute.toString();
      }
    } catch (_) {}
    return trimmed;
  }

  static String _normalizeAssetPath(String path) {
    var normalized = path;
    while (normalized.contains('/assets/assets/')) {
      normalized = normalized.replaceAll('/assets/assets/', '/assets/');
    }
    return normalized;
  }
  // ─────────────────────────────────────────────────────────────────────

  static const String loginEndpoint          = '/auth/login';
  static const String registerEndpoint       = '/auth/register';
  static const String logoutEndpoint         = '/auth/logout';
  static const String meEndpoint             = '/auth/me';
  static const String changePasswordEndpoint = '/auth/change-password';

  static const String profileEndpoint          = '/profile';
  static const String updateProfileEndpoint    = '/profile/update';
  static const String balanceEndpoint          = '/profile/balance';
  static const String recentTransactionsEndpoint = '/profile/recent-transactions';

  static const String transactionsEndpoint    = '/transactions';
  static const String depositTransactions     = '/transactions/deposits';
  static const String withdrawTransactions    = '/transactions/withdrawals';

  static const String gatewaysEndpoint        = '/deposit/gateways';
  static const String initiateDepositEndpoint = '/deposit/initiate';
  static const String manualProofEndpoint     = '/deposit/manual-proof';

  static const String withdrawMethodsEndpoint  = '/withdraw/methods';
  static const String withdrawAccountsEndpoint = '/withdraw/accounts';
  static const String withdrawDetailsEndpoint  = '/withdraw/details';
  static const String withdrawInitiateEndpoint = '/withdraw/initiate';
  static String withdrawStatusEndpoint(String tnx) => '/withdraw/status/$tnx';

  static const String notificationsEndpoint   = '/notifications';
  static String markNotificationReadEndpoint(int id) => '/notifications/$id/read';
  static const String markAllNotificationsReadEndpoint = '/notifications/read-all';

  static const String masterCardsEndpoint     = '/cards/master';
  static const String masterApplyEndpoint     = '/cards/master/apply';
  static const String visaCardsEndpoint       = '/cards/visa';
  static const String visaApplyEndpoint       = '/cards/visa/apply';
  static const String digitalCardsEndpoint    = '/cards/digital';
  static const String cardFeesEndpoint        = '/cards/fees';

  static String digitalApprove3dsEndpoint(String cardId) =>
      '$digitalCardsEndpoint/$cardId/approve-3ds';
}
