import '../config/app_config.dart';
import '../models/transaction.dart';
import 'api_service.dart';

class TransactionService {
  static Future<Map<String, dynamic>> getTransactions({
    int page = 1,
    int limit = 15,
    String? type,
    String? from,
    String? to,
    String? search,
  }) async {
    final params = <String, dynamic>{'page': page, 'limit': limit};
    if (type != null) params['type'] = type;
    if (from != null) params['from'] = from;
    if (to != null) params['to'] = to;
    if (search != null && search.isNotEmpty) params['search'] = search;

    final data = await ApiService.get(AppConfig.transactionsEndpoint, params: params);
    final items = (data['data'] as List? ?? [])
        .map((e) => Transaction.fromJson(e as Map<String, dynamic>))
        .toList();
    final meta = data['meta'] != null
        ? TransactionMeta.fromJson(data['meta'] as Map<String, dynamic>)
        : null;
    return {'transactions': items, 'meta': meta};
  }

  static Future<List<Transaction>> getDeposits({int limit = 15}) async {
    final data = await ApiService.get(
      AppConfig.depositTransactions,
      params: {'limit': limit},
    );
    return (data['data'] as List? ?? [])
        .map((e) => Transaction.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  static Future<List<Transaction>> getWithdrawals({int limit = 15}) async {
    final data = await ApiService.get(
      AppConfig.withdrawTransactions,
      params: {'limit': limit},
    );
    return (data['data'] as List? ?? [])
        .map((e) => Transaction.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  static Future<Transaction> getTransaction(String tnx) async {
    final data = await ApiService.get('${AppConfig.transactionsEndpoint}/$tnx');
    return Transaction.fromJson(data['data'] as Map<String, dynamic>);
  }
}

