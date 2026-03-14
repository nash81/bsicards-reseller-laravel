import '../config/app_config.dart';

class User {
  final int id;
  final String firstName;
  final String lastName;
  final String fullName;
  final String email;
  final String username;
  final String phone;
  final String country;
  final String? city;
  final String? address;
  final String? zipCode;
  final String? gender;
  final String? dateOfBirth;
  final String? avatar;
  final String? accountNumber;
  final double balance;
  final String currencySymbol;
  final int kyc;
  final int status;
  final bool twoFa;
  final int depositStatus;
  final int withdrawStatus;
  final int transferStatus;
  final double totalDeposit;
  final double totalWithdraw;
  final double totalProfit;

  const User({
    required this.id,
    required this.firstName,
    required this.lastName,
    required this.fullName,
    required this.email,
    required this.username,
    required this.phone,
    required this.country,
    this.city,
    this.address,
    this.zipCode,
    this.gender,
    this.dateOfBirth,
    this.avatar,
    this.accountNumber,
    required this.balance,
    this.currencySymbol = '\$',
    required this.kyc,
    required this.status,
    this.twoFa = false,
    this.depositStatus = 1,
    this.withdrawStatus = 1,
    this.transferStatus = 1,
    this.totalDeposit = 0,
    this.totalWithdraw = 0,
    this.totalProfit = 0,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] ?? 0,
      firstName: json['first_name'] ?? '',
      lastName: json['last_name'] ?? '',
      fullName: json['full_name'] ?? '',
      email: json['email'] ?? '',
      username: json['username'] ?? '',
      phone: json['phone'] ?? '',
      country: json['country'] ?? '',
      city: json['city'],
      address: json['address'],
      zipCode: json['zip_code'],
      gender: json['gender'],
      dateOfBirth: json['date_of_birth'],
      avatar: json['avatar'] != null
          ? AppConfig.fixUrl(json['avatar'] as String)
          : null,
      accountNumber: json['account_number'],
      balance: (json['balance'] ?? 0).toDouble(),
      currencySymbol: json['currency_symbol'] ?? '\$',
      kyc: json['kyc'] ?? 0,
      status: json['status'] ?? 1,
      twoFa: json['two_fa'] ?? false,
      depositStatus: json['deposit_status'] ?? 1,
      withdrawStatus: json['withdraw_status'] ?? 1,
      transferStatus: json['transfer_status'] ?? 1,
      totalDeposit: (json['total_deposit'] ?? 0).toDouble(),
      totalWithdraw: (json['total_withdraw'] ?? 0).toDouble(),
      totalProfit: (json['total_profit'] ?? 0).toDouble(),
    );
  }

  User copyWith({double? balance}) {
    return User(
      id: id, firstName: firstName, lastName: lastName,
      fullName: fullName, email: email, username: username,
      phone: phone, country: country, city: city, address: address,
      zipCode: zipCode, gender: gender, dateOfBirth: dateOfBirth,
      avatar: avatar, accountNumber: accountNumber,
      balance: balance ?? this.balance,
      currencySymbol: currencySymbol, kyc: kyc, status: status,
      twoFa: twoFa, depositStatus: depositStatus,
      withdrawStatus: withdrawStatus, transferStatus: transferStatus,
      totalDeposit: totalDeposit, totalWithdraw: totalWithdraw,
      totalProfit: totalProfit,
    );
  }
}

