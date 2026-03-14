import 'dart:io';

import '../config/app_config.dart';
import '../models/virtual_card.dart';
import 'api_service.dart';

class CardService {
  static Future<Map<String, dynamic>> getCardFees() async {
    final data = await ApiService.get(AppConfig.cardFeesEndpoint);
    return (data['data'] as Map<String, dynamic>? ?? <String, dynamic>{});
  }

  // ── Master Cards ─────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getMasterCards() async {
    final data = await ApiService.get(AppConfig.masterCardsEndpoint);
    final cards = _parseCardList(data['cards'], 'master');
    final pending = _parsePendingList(data['pending']);
    return {'cards': cards, 'pending': pending};
  }

  static Future<Map<String, dynamic>> getMasterCardDetail(String cardId) async {
    final data = await ApiService.get('${AppConfig.masterCardsEndpoint}/$cardId');
    final cardJson = _parseCardDetail(data);
    return {
      'card': cardJson != null
          ? VirtualCard.fromJson(cardJson, type: 'master')
          : null,
      'transactions': _parseTransactions(data['transactions']),
    };
  }

  static Future<void> masterLoadFunds(String cardId, double amount) async {
    await ApiService.post(
      '${AppConfig.masterCardsEndpoint}/load',
      body: {'cardid': cardId, 'amount': amount},
    );
  }

  static Future<void> masterBlockCard(String cardId) async =>
      ApiService.post('${AppConfig.masterCardsEndpoint}/$cardId/block');

  static Future<void> masterUnblockCard(String cardId) async =>
      ApiService.post('${AppConfig.masterCardsEndpoint}/$cardId/unblock');

  static Future<Map<String, dynamic>> applyMasterCard({
    required String pin,
  }) async {
    return ApiService.post(
      AppConfig.masterApplyEndpoint,
      body: {'pin': pin},
    );
  }

  // ── Visa Cards ───────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getVisaCards() async {
    final data = await ApiService.get(AppConfig.visaCardsEndpoint);
    final cards = _parseCardList(data['cards'], 'visa');
    final pending = _parsePendingList(data['pending']);
    return {'cards': cards, 'pending': pending};
  }

  static Future<Map<String, dynamic>> getVisaCardDetail(String cardId) async {
    final data = await ApiService.get('${AppConfig.visaCardsEndpoint}/$cardId');
    final cardJson = _parseCardDetail(data);
    return {
      'card': cardJson != null
          ? VirtualCard.fromJson(cardJson, type: 'visa')
          : null,
      'transactions': _parseTransactions(data['transactions']),
    };
  }

  static Future<void> visaLoadFunds(String cardId, double amount) async {
    await ApiService.post(
      '${AppConfig.visaCardsEndpoint}/load',
      body: {'cardid': cardId, 'amount': amount},
    );
  }

  static Future<void> visaBlockCard(String cardId) async =>
      ApiService.post('${AppConfig.visaCardsEndpoint}/$cardId/block');

  static Future<void> visaUnblockCard(String cardId) async =>
      ApiService.post('${AppConfig.visaCardsEndpoint}/$cardId/unblock');

  static Future<Map<String, dynamic>> applyVisaCard({
    required String pin,
    required String dob,
    required String nationalIdNumber,
    File? userPhotoFile,
    File? nationalIdImageFile,
    String? userPhotoUrl,
    String? nationalIdImageUrl,
  }) async {
    if (userPhotoFile != null && nationalIdImageFile != null) {
      return ApiService.postMultipart(
        AppConfig.visaApplyEndpoint,
        fields: {
          'pin': pin,
          'dob': dob,
          'nationalidnumber': nationalIdNumber,
        },
        files: {
          'userphoto': userPhotoFile,
          'nationalidimage': nationalIdImageFile,
        },
      );
    }

    return ApiService.post(
      AppConfig.visaApplyEndpoint,
      body: {
        'pin': pin,
        'dob': dob,
        'nationalidnumber': nationalIdNumber,
        if (userPhotoUrl != null) 'userphoto': userPhotoUrl,
        if (nationalIdImageUrl != null) 'nationalidimage': nationalIdImageUrl,
      },
    );
  }

  // ── Digital Mastercards ──────────────────────────────────────────────
  static Future<List<VirtualCard>> getDigitalCards() async {
    final data = await ApiService.get(AppConfig.digitalCardsEndpoint);
    return _parseCardList(data['cards'], 'digital');
  }

  static Future<Map<String, dynamic>> getDigitalCardDetail(String cardId) async {
    final data = await ApiService.get('${AppConfig.digitalCardsEndpoint}/$cardId');
    final cardJson = _parseCardDetail(data);
    final mergedCard = <String, dynamic>{
      if (cardJson != null) ...cardJson,
    };
    final addonRaw = data['addon'] ??
        data['addoncard'] ??
        mergedCard['addon'] ??
        mergedCard['addoncard'];

    if (addonRaw != null) {
      mergedCard['addon'] = addonRaw;
    }

    return {
      'card': mergedCard.isNotEmpty
          ? VirtualCard.fromJson(mergedCard, type: 'digital')
          : null,
      'transactions': _parseTransactions(data['transactions'] ?? mergedCard['transactions']),
      'deposits': _parseList(data['deposits'] ?? mergedCard['deposits']),
      'points': _parseList(data['points'] ?? mergedCard['points']),
      'addons': _parseCardList(addonRaw, 'digital'),
      'check3ds': data['check3ds'],
    };
  }

  static Future<void> digitalLoadFunds(String cardId, double amount) async {
    await ApiService.post(
      '${AppConfig.digitalCardsEndpoint}/load',
      body: {'cardid': cardId, 'amount': amount},
    );
  }

  static Future<void> digitalBlockCard(String cardId) async =>
      ApiService.post('${AppConfig.digitalCardsEndpoint}/$cardId/block');

  static Future<void> digitalUnblockCard(String cardId) async =>
      ApiService.post('${AppConfig.digitalCardsEndpoint}/$cardId/unblock');

  static Future<Map<String, dynamic>> applyDigitalCard({
    required String userEmail,
    required String firstName,
    required String lastName,
    required String dob,
    required String address1,
    required String city,
    required String country,
    required String state,
    required String postalCode,
    required String countryCode,
    required String phone,
  }) async {
    return ApiService.post(
      '${AppConfig.digitalCardsEndpoint}/apply',
      body: {
        'useremail': userEmail,
        'firstname': firstName,
        'lastname': lastName,
        'dob': dob,
        'address1': address1,
        'city': city,
        'country': country,
        'state': state,
        'postalcode': postalCode,
        'countrycode': countryCode,
        'phone': phone,
      },
    );
  }

  static Future<Map<String, dynamic>> applyAddonCard(String cardId) async {
    return ApiService.post(
      '${AppConfig.digitalCardsEndpoint}/addon',
      body: {'cardid': cardId},
    );
  }

  static Future<Map<String, dynamic>> check3ds(String cardId) async {
    final data =
        await ApiService.get('${AppConfig.digitalCardsEndpoint}/$cardId/check-3ds');
    return data;
  }

  static Future<Map<String, dynamic>> approve3ds({
    required String cardId,
    required String eventId,
  }) async {
    return ApiService.post(
      AppConfig.digitalApprove3dsEndpoint(cardId),
      body: {'eventId': eventId},
    );
  }

  static Future<Map<String, dynamic>> getWalletOtp(String cardId) async {
    final data =
        await ApiService.get('${AppConfig.digitalCardsEndpoint}/$cardId/wallet-otp');
    return data;
  }

  // ── Helpers ──────────────────────────────────────────────────────────
  static List<VirtualCard> _parseCardList(dynamic raw, String type) {
    if (raw == null) return [];

    // Direct list
    if (raw is List) {
      return raw
          .whereType<Map<String, dynamic>>()
          .map((e) => VirtualCard.fromJson(e, type: type))
          .toList();
    }

    // BSI sometimes wraps the list in an object: { "cards": [...] } or { "data": [...] }
    if (raw is Map<String, dynamic>) {
      for (final key in ['cards', 'data', 'result', 'list']) {
        final nested = raw[key];
        if (nested is List) {
          return nested
              .whereType<Map<String, dynamic>>()
              .map((e) => VirtualCard.fromJson(e, type: type))
              .toList();
        }
      }

      // Some payloads send one addon card object instead of a list.
      if (raw.containsKey('cardid') || raw.containsKey('card_number')) {
        return [VirtualCard.fromJson(raw, type: type)];
      }
    }

    return [];
  }

  static List<Map<String, dynamic>> _parsePendingList(dynamic raw) {
    if (raw == null) return [];
    if (raw is List) {
      return raw.whereType<Map<String, dynamic>>().toList();
    }
    if (raw is Map<String, dynamic>) {
      for (final key in ['pending', 'data', 'result', 'list']) {
        final nested = raw[key];
        if (nested is List) {
          return nested.whereType<Map<String, dynamic>>().toList();
        }
      }
    }
    return [];
  }

  static List<dynamic> _parseList(dynamic raw) {
    if (raw == null) return [];
    if (raw is List) return raw;

    if (raw is Map<String, dynamic>) {
      for (final key in ['data', 'items', 'list', 'result']) {
        final nested = raw[key];
        if (nested is List) return nested;
      }

      final response = raw['response'];
      if (response is Map<String, dynamic>) {
        for (final key in ['items', 'data', 'list', 'result']) {
          final nested = response[key];
          if (nested is List) return nested;
        }
      }
    }

    return [];
  }

  static Map<String, dynamic>? _parseCardDetail(Map<String, dynamic> data) {
    final directCard = data['card'];
    if (directCard is Map<String, dynamic>) return directCard;

    final nestedData = data['data'];
    if (nestedData is Map<String, dynamic>) {
      final nestedCard = nestedData['card'];
      if (nestedCard is Map<String, dynamic>) return nestedCard;
      // Some providers return the card fields directly inside `data`.
      return nestedData;
    }

    // Fallback: some responses are already the card object at root level.
    if (data.containsKey('cardid') || data.containsKey('card_number')) {
      return data;
    }

    return null;
  }

  static List<dynamic> _parseTransactions(dynamic raw) {
    if (raw == null) return [];
    if (raw is List) return raw;

    if (raw is Map<String, dynamic>) {
      for (final key in ['items', 'cardTransactions', 'transactions']) {
        final direct = raw[key];
        if (direct is List) return direct;
      }

      final response = raw['response'];
      if (response is Map<String, dynamic>) {
        final items = response['items'];
        if (items is List) return items;

        final data = response['data'];
        if (data is Map<String, dynamic>) {
          for (final key in ['items', 'cardTransactions', 'transactions']) {
            final tx = data[key];
            if (tx is List) return tx;
          }
        }
      }

      final data = raw['data'];
      if (data is Map<String, dynamic>) {
        for (final key in ['items', 'cardTransactions', 'transactions']) {
          final tx = data[key];
          if (tx is List) return tx;
        }
      }
    }

    return [];
  }
}

