class Gateway {
  final int id;
  final String name;
  final String gatewayCode;
  final String logo;
  final String currency;
  final double minimumDeposit;
  final double maximumDeposit;
  final double charge;
  final String chargeType;
  final double rate;
  final String type;

  const Gateway({
    required this.id,
    required this.name,
    required this.gatewayCode,
    required this.logo,
    required this.currency,
    required this.minimumDeposit,
    required this.maximumDeposit,
    required this.charge,
    required this.chargeType,
    required this.rate,
    required this.type,
  });

  factory Gateway.fromJson(Map<String, dynamic> json) {
    return Gateway(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      gatewayCode: json['gateway_code'] ?? '',
      logo: json['logo'] ?? '',
      currency: json['currency'] ?? 'USD',
      minimumDeposit: (json['minimum_deposit'] ?? 0).toDouble(),
      maximumDeposit: (json['maximum_deposit'] ?? 9999).toDouble(),
      charge: (json['charge'] ?? 0).toDouble(),
      chargeType: json['charge_type'] ?? 'fixed',
      rate: (json['rate'] ?? 1).toDouble(),
      type: json['type'] ?? 'auto',
    );
  }

  bool get isManual => type == 'manual';

  double calculateCharge(double amount) {
    if (chargeType == 'percentage') return (charge / 100) * amount;
    return charge;
  }

  double calculateTotal(double amount) => amount + calculateCharge(amount);
}

