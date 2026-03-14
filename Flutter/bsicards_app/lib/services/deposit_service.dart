import 'dart:io';
import 'package:flutter/foundation.dart';
import '../config/app_config.dart';
import '../models/gateway.dart';
import 'api_service.dart';

class DepositService {
  static Future<List<Gateway>> getGateways() async {
    final data = await ApiService.get(AppConfig.gatewaysEndpoint);
    return (data['data'] as List? ?? [])
        .map((e) => Gateway.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  static Future<Map<String, dynamic>> initiateDeposit({
    required String gatewayCode,
    required double amount,
  }) async {
    final response = await ApiService.post(
      AppConfig.initiateDepositEndpoint,
      body: {'gateway_code': gatewayCode, 'amount': amount},
    );
    if (kDebugMode) {
      debugPrint('💳 Deposit API response: $response');
    }
    return response;
  }

  static Future<Map<String, dynamic>> getDepositStatus(String tnx) async {
    return ApiService.get('/deposit/status/$tnx');
  }

  static Future<void> submitManualProof({
    required String tnx,
    String? proof,
    Map<String, String>? manualFields,
  }) async {
    final fields = <String, String>{'tnx': tnx};
    (manualFields ?? {}).forEach((key, value) {
      fields['manual_fields[$key]'] = value;
    });

    await ApiService.postMultipart(
      AppConfig.manualProofEndpoint,
      fields: fields,
      files: proof != null ? {'proof': File(proof)} : null,
    );
  }
}

