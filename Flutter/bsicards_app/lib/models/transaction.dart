import 'package:flutter/material.dart';

class Transaction {
  final int id;
  final String tnx;
  final String type;
  final String description;
  final String method;
  final double amount;
  final double charge;
  final double finalAmount;
  final String? payCurrency;
  final double payAmount;
  final String status;
  final String createdAt;

  const Transaction({
    required this.id,
    required this.tnx,
    required this.type,
    required this.description,
    required this.method,
    required this.amount,
    required this.charge,
    required this.finalAmount,
    this.payCurrency,
    required this.payAmount,
    required this.status,
    required this.createdAt,
  });

  factory Transaction.fromJson(Map<String, dynamic> json) {
    return Transaction(
      id: _asInt(json['id']),
      tnx: json['tnx']?.toString() ?? '',
      type: json['type']?.toString() ?? '',
      description: json['description']?.toString() ?? '',
      method: json['method']?.toString() ?? '',
      amount: _asDouble(json['amount']),
      charge: _asDouble(json['charge']),
      finalAmount: _asDouble(json['final_amount']),
      payCurrency: json['pay_currency']?.toString(),
      payAmount: _asDouble(json['pay_amount']),
      status: json['status']?.toString() ?? '',
      createdAt: json['created_at']?.toString() ?? '',
    );
  }

  bool get isCredit =>
      type == 'deposit' || type == 'manual_deposit' ||
      type == 'signup_bonus' || type == 'referral' || type == 'receive_money';

  IconData get typeIcon {
    switch (type) {
      case 'deposit':
      case 'manual_deposit':
        return Icons.arrow_downward_rounded;
      case 'withdraw':
      case 'withdraw_auto':
        return Icons.arrow_upward_rounded;
      case 'send_money':
        return Icons.send_rounded;
      case 'receive_money':
        return Icons.call_received_rounded;
      case 'subtract':
        return Icons.credit_card_rounded;
      default:
        return Icons.swap_horiz_rounded;
    }
  }

  static double _asDouble(dynamic value) {
    if (value == null) return 0;
    if (value is num) return value.toDouble();
    return double.tryParse(value.toString()) ?? 0;
  }

  static int _asInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is num) return value.toInt();
    return int.tryParse(value.toString()) ?? 0;
  }
}

// ignore: avoid_classes_with_only_static_members
class TransactionMeta {
  final int currentPage;
  final int lastPage;
  final int perPage;
  final int total;

  const TransactionMeta({
    required this.currentPage,
    required this.lastPage,
    required this.perPage,
    required this.total,
  });

  factory TransactionMeta.fromJson(Map<String, dynamic> json) {
    return TransactionMeta(
      currentPage: json['current_page'] ?? 1,
      lastPage: json['last_page'] ?? 1,
      perPage: json['per_page'] ?? 15,
      total: json['total'] ?? 0,
    );
  }
}
