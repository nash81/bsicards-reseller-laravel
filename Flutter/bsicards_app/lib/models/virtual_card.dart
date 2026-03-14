import 'package:flutter/material.dart';

class VirtualCard {
  final String cardId;
  final String userEmail;
  final String? lastFour;
  final String? cardNumber;
  final String? cardHolder;
  final String? expiryDate;
  final String? cvv;
  final String? status;
  final String? cardType; // master | visa | digital
  final String? providerType;
  final double? balance;
  final String? billingAddress1;
  final String? billingCity;
  final String? billingState;
  final String? billingCountry;
  final String? billingZipCode;
  final List<CardDepositAddress> depositAddresses;
  final bool? isAddon;
  final List<dynamic>? addon;

  const VirtualCard({
    required this.cardId,
    required this.userEmail,
    this.lastFour,
    this.cardNumber,
    this.cardHolder,
    this.expiryDate,
    this.cvv,
    this.status,
    this.cardType,
    this.providerType,
    this.balance,
    this.billingAddress1,
    this.billingCity,
    this.billingState,
    this.billingCountry,
    this.billingZipCode,
    this.depositAddresses = const [],
    this.isAddon,
    this.addon,
  });

  factory VirtualCard.fromJson(Map<String, dynamic> json, {String type = 'digital'}) {
    final billing = json['billing_address'] is Map<String, dynamic>
        ? json['billing_address'] as Map<String, dynamic>
        : <String, dynamic>{};
    final providerType = (_asString(json['type']) ?? _asString(json['card_type']) ?? '').toLowerCase();

    final cardNumber = _asString(json['cardnumber']) ??
        _asString(json['cardNumber']) ??
        _asString(json['card_number']) ??
        _asString(json['pan']);

    return VirtualCard(
      cardId: _asString(json['cardid']) ?? _asString(json['cardId']) ?? _asString(json['card_id']) ?? '',
      userEmail: _asString(json['useremail']) ?? _asString(json['userEmail']) ?? _asString(json['user_email']) ?? '',
      lastFour: _asString(json['lastfour']) ?? _asString(json['lastFour']) ?? _asString(json['last_four']),
      cardNumber: cardNumber,
      cardHolder: _asString(json['cardholder']) ??
          _asString(json['cardHolder']) ??
          _asString(json['nameoncard']) ??
          _asString(json['name_on_card']),
      expiryDate: _asString(json['expirydate']) ??
          _asString(json['expiryDate']) ??
          _asString(json['expiry']) ??
          _asString(json['expiry_date']) ??
          _buildExpiryFromParts(json),
      cvv: _asString(json['cvv']) ?? _asString(json['cvv2']) ?? _asString(json['security_code']),
      status: _asString(json['status']) ?? _statusFromActiveFlag(json),
      cardType: type,
      providerType: _asString(json['type']) ?? _asString(json['card_type']),
      balance: _toDouble(json['balance']) ?? _fromCents(json['available_balance']),
      billingAddress1: _asString(billing['billing_address1']) ?? _asString(json['billing_address1']) ?? _asString(json['address1']),
      billingCity: _asString(billing['billing_city']) ?? _asString(json['billing_city']) ?? _asString(json['city']),
      billingState: _asString(billing['state']) ?? _asString(billing['billing_state']) ?? _asString(json['state']),
      billingCountry: _asString(billing['billing_country']) ?? _asString(json['billing_country']) ?? _asString(json['country']),
      billingZipCode: _asString(billing['billing_zip_code']) ?? _asString(json['billing_zip_code']) ?? _asString(json['postalCode']) ?? _asString(json['postalcode']),
      depositAddresses: _extractDepositAddresses(json),
      isAddon: json['isaddon'] == 1 ||
          json['isaddon'] == true ||
          json['is_addon'] == 1 ||
          json['is_addon'] == true ||
          providerType == 'virtual-addon',
      addon: _extractAddonList(json),
    );
  }

  String get maskedNumber {
    if (cardNumber != null && cardNumber!.length >= 4) {
      final last = cardNumber!.substring(cardNumber!.length - 4);
      return '**** **** **** $last';
    }
    if (lastFour != null) return '**** **** **** $lastFour';
    return '**** **** **** ****';
  }

  String get fullNumber {
    if (cardNumber != null && cardNumber!.trim().isNotEmpty) return cardNumber!;
    return maskedNumber;
  }

  String get cardTypeBadgeLabel {
    if (cardType == 'visa') return 'VISA';
    if (cardType == 'master') return 'MASTERCARD';

    // Digital cards: show Virtual-Addon or Virtual based on isaddon flag
    if (cardType == 'digital') {
      return (isAddon == true) ? 'VIRTUAL ADDON' : 'VIRTUAL';
    }

    final provider = _asString(providerType);
    if (provider != null) {
      return provider.replaceAll('-', ' ').replaceAll('_', ' ').toUpperCase();
    }

    return 'DIGITAL MC';
  }

  String get cardTypeTitleLabel {
    if (cardType == 'visa') return 'Visa';
    if (cardType == 'master') return 'Mastercard';

    final provider = _asString(providerType);
    if (provider != null) {
      return provider
          .replaceAll('-', ' ')
          .replaceAll('_', ' ')
          .split(' ')
          .where((part) => part.trim().isNotEmpty)
          .map((part) {
            final lower = part.toLowerCase();
            return lower[0].toUpperCase() + lower.substring(1);
          })
          .join(' ');
    }

    return 'Digital';
  }

  bool get isBlocked {
    final normalized = status?.trim().toLowerCase();
    if (normalized == null || normalized.isEmpty) {
      return false;
    }
    if (cardType == 'digital') {
      return normalized != 'active' && normalized != 'success';
    }
    return normalized == 'blocked' || normalized == 'inactive' || normalized == 'disabled';
  }

  String get formattedBillingAddress {
    final parts = [billingAddress1, billingCity, billingState, billingZipCode, billingCountry]
        .whereType<String>()
        .where((v) => v.trim().isNotEmpty)
        .toList();
    return parts.isEmpty ? 'Billing address unavailable' : parts.join(', ');
  }

  Color get cardGradientStart {
    switch (cardType) {
      case 'visa':
        return const Color(0xFF1A237E);
      case 'master':
        return const Color(0xFF1B1B2F);
      default:
        return const Color(0xFF0D47A1);
    }
  }

  Color get cardGradientEnd {
    switch (cardType) {
      case 'visa':
        return const Color(0xFF283593);
      case 'master':
        return const Color(0xFF374151);
      default:
        return const Color(0xFF1565C0);
    }
  }

  static String? _asString(dynamic value) {
    if (value == null) return null;
    final text = value.toString().trim();
    return text.isEmpty ? null : text;
  }

  static String? _buildExpiryFromParts(Map<String, dynamic> json) {
    final month = _asString(json['expirymonth']) ?? _asString(json['expiry_month']);
    final year = _asString(json['expiryyear']) ?? _asString(json['expiry_year']);
    if (month == null || year == null) return null;
    final mm = month.padLeft(2, '0');
    final yy = year.length >= 2 ? year.substring(year.length - 2) : year.padLeft(2, '0');
    return '$mm/$yy';
  }

  static double? _toDouble(dynamic value) {
    if (value == null) return null;
    if (value is num) return value.toDouble();
    return double.tryParse(value.toString());
  }

  static double? _fromCents(dynamic value) {
    final cents = _toDouble(value);
    if (cents == null) return null;
    return cents / 100;
  }

  static String? _statusFromActiveFlag(Map<String, dynamic> json) {
    final active = json['is_active'];
    if (active == null) return null;
    final normalized = active.toString().trim().toLowerCase();
    if (active == true || active == 1 || normalized == 'true' || normalized == 'active') {
      return 'active';
    }
    return 'blocked';
  }

  static List<CardDepositAddress> _extractDepositAddresses(Map<String, dynamic> json) {
    const keyMap = {
      'depositaddress': 'USDC',
      'usdtdepositaddress': 'USDT',
      'btcdepositaddress': 'BTC',
      'ethdepositaddress': 'ETH',
      'soldepositaddress': 'SOL',
      'bnbdepositaddress': 'BNB',
      'xrpdepositaddress': 'XRP',
      'paxgdepositaddress': 'PAXG',
    };

    final list = <CardDepositAddress>[];
    keyMap.forEach((key, asset) {
      final raw = _asString(json[key]);
      if (raw == null) return;
      list.add(CardDepositAddress.fromRaw(asset: asset, raw: raw));
    });
    return list;
  }

  static List<dynamic>? _extractAddonList(Map<String, dynamic> json) {
    for (final key in ['addon', 'addoncard']) {
      final value = json[key];
      if (value is List) return value;
    }
    return null;
  }
}

class CardDepositAddress {
  final String asset;
  final String network;
  final String address;
  final String raw;

  const CardDepositAddress({
    required this.asset,
    required this.network,
    required this.address,
    required this.raw,
  });

  factory CardDepositAddress.fromRaw({required String asset, required String raw}) {
    final parts = raw.split('-');
    if (parts.length >= 3) {
      final address = parts.sublist(2).join('-').trim();
      return CardDepositAddress(
        asset: asset,
        network: parts[1].trim(),
        address: address.isEmpty ? raw : address,
        raw: raw,
      );
    }

    if (parts.length == 2) {
      final address = parts[1].trim();
      return CardDepositAddress(
        asset: asset,
        network: parts[0].trim(),
        address: address.isEmpty ? raw : address,
        raw: raw,
      );
    }

    return CardDepositAddress(asset: asset, network: asset, address: raw, raw: raw);
  }
}
