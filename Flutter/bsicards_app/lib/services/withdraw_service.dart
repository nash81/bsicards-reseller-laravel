import 'dart:convert';
import 'dart:io';

import '../config/app_config.dart';
import 'api_service.dart';

class WithdrawService {
  static Future<List<Map<String, dynamic>>> getMethods() async {
    final data = await ApiService.get(AppConfig.withdrawMethodsEndpoint);
    return (data['data'] as List? ?? [])
        .whereType<Map<String, dynamic>>()
        .map(_normalizeMethod)
        .toList();
  }

  static Future<List<Map<String, dynamic>>> getAccounts() async {
    final data = await ApiService.get(AppConfig.withdrawAccountsEndpoint);
    return (data['data'] as List? ?? [])
        .whereType<Map<String, dynamic>>()
        .map((account) {
          final normalized = Map<String, dynamic>.from(account);
          final rawCreds = normalized['credentials'];
          normalized['credentials'] = _asMap(rawCreds);
          return normalized;
        })
        .toList();
  }

  static Map<String, dynamic> _normalizeMethod(Map<String, dynamic> method) {
    final normalized = Map<String, dynamic>.from(method);
    final rawFields = normalized['fields'];
    normalized['fields'] = _asFieldList(rawFields);
    return normalized;
  }

  static List<Map<String, dynamic>> _asFieldList(dynamic raw) {
    if (raw is List) {
      return raw.whereType<Map<String, dynamic>>().toList();
    }
    if (raw is Map) {
      final fields = <Map<String, dynamic>>[];
      raw.forEach((key, value) {
        if (value is Map<String, dynamic>) {
          fields.add({
            'label': (value['label'] ?? key).toString(),
            'name': (value['name'] ?? key).toString(),
            'type': (value['type'] ?? 'text').toString(),
            'validation': (value['validation'] ?? 'required').toString(),
          });
        }
      });
      return fields;
    }
    if (raw is String) {
      try {
        final decoded = jsonDecode(raw);
        if (decoded is List) {
          return decoded.whereType<Map<String, dynamic>>().toList();
        }
        if (decoded is Map) {
          return _asFieldList(decoded);
        }
      } catch (_) {}
    }
    return [];
  }

  static Map<String, dynamic> _asMap(dynamic raw) {
    if (raw is Map<String, dynamic>) return raw;
    if (raw is String) {
      try {
        final decoded = jsonDecode(raw);
        if (decoded is Map<String, dynamic>) return decoded;
      } catch (_) {}
    }
    return <String, dynamic>{};
  }

  static Future<Map<String, dynamic>> createAccount({
    required int withdrawMethodId,
    required String methodName,
    required Map<String, Map<String, dynamic>> credentials,
    Map<String, String>? filePaths,
  }) async {
    final fields = <String, String>{
      'withdraw_method_id': withdrawMethodId.toString(),
      'method_name': methodName,
      'credentials': jsonEncode(credentials),
    };

    final files = <String, File>{};
    (filePaths ?? {}).forEach((key, path) {
      if (path.trim().isNotEmpty) {
        files['credentials[$key][value]'] = File(path);
      }
    });

    return ApiService.postMultipart(
      AppConfig.withdrawAccountsEndpoint,
      fields: fields,
      files: files.isEmpty ? null : files,
    );
  }

  static Future<Map<String, dynamic>> updateAccount({
    required int accountId,
    required int withdrawMethodId,
    required String methodName,
    required Map<String, Map<String, dynamic>> credentials,
    Map<String, String>? filePaths,
  }) async {
    final fields = <String, String>{
      'withdraw_method_id': withdrawMethodId.toString(),
      'method_name': methodName,
      'credentials': jsonEncode(credentials),
    };

    final files = <String, File>{};
    (filePaths ?? {}).forEach((key, path) {
      if (path.trim().isNotEmpty) {
        files['credentials[$key][value]'] = File(path);
      }
    });

    return ApiService.postMultipart(
      '${AppConfig.withdrawAccountsEndpoint}/$accountId',
      fields: fields,
      files: files.isEmpty ? null : files,
    );
  }

  static Future<Map<String, dynamic>> deleteAccount(int accountId) {
    return ApiService.delete('${AppConfig.withdrawAccountsEndpoint}/$accountId');
  }

  static Future<Map<String, dynamic>> getDetails({
    required int withdrawAccountId,
    double? amount,
  }) async {
    final params = <String, dynamic>{
      'withdraw_account_id': withdrawAccountId,
    };
    if (amount != null) {
      params['amount'] = amount;
    }

    final data = await ApiService.get(
      AppConfig.withdrawDetailsEndpoint,
      params: params,
    );

    return (data['data'] as Map<String, dynamic>? ?? <String, dynamic>{});
  }

  static Future<Map<String, dynamic>> initiate({
    required int withdrawAccountId,
    required double amount,
  }) async {
    return ApiService.post(
      AppConfig.withdrawInitiateEndpoint,
      body: {
        'withdraw_account_id': withdrawAccountId,
        'amount': amount,
      },
    );
  }
}

