import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';

import '../../config/app_colors.dart';
import '../../config/app_theme.dart';
import '../../data/countries.dart';
import '../../l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../../services/auth_service.dart';
import '../../widgets/common_widgets.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  final _firstCtrl   = TextEditingController();
  final _lastCtrl    = TextEditingController();
  final _phoneCtrl   = TextEditingController();
  final _countryCtrl = TextEditingController();
  final _cityCtrl    = TextEditingController();
  final _zipCtrl     = TextEditingController();
  final _addressCtrl = TextEditingController();
  bool _saving = false;
  File? _newAvatar;

  String _displayCountry(String rawCountry) {
    final value = rawCountry.trim();
    if (value.isEmpty) return '';
    for (final country in allCountries) {
      if (country.code.toLowerCase() == value.toLowerCase()) {
        return country.name;
      }
      if (country.name.toLowerCase() == value.toLowerCase()) {
        return country.name;
      }
    }
    return value;
  }

  @override
  void initState() {
    super.initState();
    _prefill();
  }

  void _prefill() {
    final user = context.read<AuthProvider>().user;
    if (user == null) return;
    _firstCtrl.text   = user.firstName;
    _lastCtrl.text    = user.lastName;
    _phoneCtrl.text   = user.phone;
    _countryCtrl.text = _displayCountry(user.country);
    _cityCtrl.text    = user.city ?? '';
    _zipCtrl.text     = user.zipCode ?? '';
    _addressCtrl.text = user.address ?? '';
  }

  Future<void> _pickCountry() async {
    final tr = context.tr;
    final colors = context.colors;
    final searchCtrl = TextEditingController();
    var filtered = List<CountryOption>.from(allCountries);

    final picked = await showModalBottomSheet<String>(
      context: context,
      isScrollControlled: true,
      backgroundColor: colors.bgCard,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (sheetCtx) => StatefulBuilder(
        builder: (ctx, setModal) {
          final maxSheetHeight = MediaQuery.of(sheetCtx).size.height * 0.78;
          return SafeArea(
            child: Padding(
              padding: EdgeInsets.only(
                left: 16,
                right: 16,
                top: 16,
                bottom: MediaQuery.of(sheetCtx).viewInsets.bottom + 16,
              ),
              child: SizedBox(
                height: maxSheetHeight,
                child: Column(
                  children: [
                    Text(
                      tr('select_country'),
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                        color: colors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: searchCtrl,
                      style: TextStyle(color: colors.textPrimary),
                      decoration: InputDecoration(
                        hintText: tr('type_to_search'),
                        prefixIcon: const Icon(Icons.search),
                      ),
                      onChanged: (value) {
                        final q = value.trim().toLowerCase();
                        setModal(() {
                          filtered = q.isEmpty
                              ? List<CountryOption>.from(allCountries)
                              : allCountries.where((country) {
                                  final haystack = '${country.name} ${country.code}'.toLowerCase();
                                  return haystack.contains(q);
                                }).toList();
                        });
                      },
                    ),
                    const SizedBox(height: 10),
                    Expanded(
                      child: filtered.isEmpty
                          ? Center(
                              child: Text(
                                tr('no_matching_results'),
                                style: TextStyle(color: colors.textSecondary),
                              ),
                            )
                          : ListView.separated(
                              itemCount: filtered.length,
                              separatorBuilder: (_, __) => Divider(height: 1, color: colors.divider),
                              itemBuilder: (_, i) {
                                final country = filtered[i];
                                return ListTile(
                                  title: Text(
                                    country.name,
                                    style: TextStyle(color: colors.textPrimary),
                                  ),
                                  subtitle: Text(
                                    country.code,
                                    style: TextStyle(color: colors.textSecondary, fontSize: 12),
                                  ),
                                  onTap: () => Navigator.pop(sheetCtx, country.name),
                                );
                              },
                            ),
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );

    if (!mounted || picked == null || picked.isEmpty) return;
    setState(() => _countryCtrl.text = picked);
  }

  @override
  void dispose() {
    for (final c in [
      _firstCtrl,
      _lastCtrl,
      _phoneCtrl,
      _countryCtrl,
      _cityCtrl,
      _zipCtrl,
      _addressCtrl,
    ]) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _pickImage() async {
    final img = await ImagePicker().pickImage(
        source: ImageSource.gallery, imageQuality: 70, maxWidth: 600);
    if (img != null) setState(() => _newAvatar = File(img.path));
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);
    try {
      final Map<String, String> fields = {
        'first_name': _firstCtrl.text,
        'last_name': _lastCtrl.text,
        'phone': _phoneCtrl.text,
        'country': _countryCtrl.text,
        'city': _cityCtrl.text,
        'zip_code': _zipCtrl.text,
        'address': _addressCtrl.text,
      };

      final files = _newAvatar != null ? {'avatar': _newAvatar!} : null;

      debugPrint('📝 Updating profile with fields: $fields');
      debugPrint('📁 Avatar file: ${_newAvatar?.path ?? "None"}');

      await context.read<AuthProvider>().updateProfile(fields: fields, files: files);

      debugPrint('✅ Profile updated successfully');

      if (!mounted) return;
      // Re-prefill controllers with authoritative server values
      _prefill();

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(context.tr('profile_updated')),
          backgroundColor: AppTheme.success,
          behavior: SnackBarBehavior.floating,
        ),
      );
      setState(() => _newAvatar = null);
    } catch (e) {
      debugPrint('❌ Profile update error: $e');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(e.toString()),
            backgroundColor: AppTheme.error,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
    if (mounted) setState(() => _saving = false);
  }

  Future<void> _logout() async {
    final colors = context.colors;
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: colors.bgCard,
        title: Text(context.tr('logout'), style: TextStyle(color: colors.textPrimary)),
        content: Text(context.tr('confirm_logout'),
            style: TextStyle(color: colors.textSecondary)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: Text(context.tr('cancel')),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: Text(context.tr('logout'), style: const TextStyle(color: AppTheme.error)),
          ),
        ],
      ),
    );
    if (confirm == true && mounted) {
      await context.read<AuthProvider>().logout();
    }
  }

  @override
  Widget build(BuildContext context) {
    final tr = context.tr;
    final colors = context.colors;
    final user = context.watch<AuthProvider>().user;
    return Scaffold(
      backgroundColor: colors.bgDark,
      appBar: AppBar(
        title: Text(tr('my_profile')),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout_rounded, color: AppTheme.error),
            onPressed: _logout,
            tooltip: tr('logout'),
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Avatar
              Center(
                child: Stack(
                  children: [
                    GestureDetector(
                      onTap: _pickImage,
                      child: Container(
                        width: 100, height: 100,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: colors.surfaceLight,
                          border: Border.all(color: colors.primary, width: 2),
                          image: _newAvatar != null
                              ? DecorationImage(
                                  image: FileImage(_newAvatar!),
                                  fit: BoxFit.cover,
                                )
                              : user?.avatar != null
                                  ? DecorationImage(
                                      image: NetworkImage(user!.avatar!),
                                      fit: BoxFit.cover,
                                    )
                                  : null,
                        ),
                        child: (_newAvatar == null && user?.avatar == null)
                            ? Text(
                                (user?.firstName ?? 'U')
                                    .substring(0, 1)
                                    .toUpperCase(),
                                style: TextStyle(
                                    fontSize: 36,
                                    fontWeight: FontWeight.bold,
                                    color: colors.primary),
                              )
                            : null,
                      ),
                    ),
                    Positioned(
                      bottom: 0, right: 0,
                      child: Container(
                        padding: const EdgeInsets.all(6),
                        decoration: BoxDecoration(
                          color: colors.primary,
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(Icons.camera_alt, color: Colors.white, size: 14),
                      ),
                    ),
                  ],
                ).animate().scale(duration: 400.ms, curve: Curves.elasticOut),
              ),
              const SizedBox(height: 8),
              if (user != null)
                Center(
                  child: Column(
                    children: [
                      Text(user.email,
                          style: TextStyle(
                              color: colors.textSecondary, fontSize: 13)),
                      const SizedBox(height: 4),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 12, vertical: 4),
                        decoration: BoxDecoration(
                          color: colors.primary.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          '${tr('account')}: ${user.accountNumber ?? tr('not_available')}',
                          style: TextStyle(
                              color: colors.primary,
                              fontSize: 12,
                              fontWeight: FontWeight.w600),
                        ),
                      ),
                    ],
                  ),
                ),
              const SizedBox(height: 32),
              // Balance card
              if (user != null) _balanceRow(user),
              const SizedBox(height: 32),
              Text(tr('personal_information'),
                  style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                      color: colors.textPrimary))
                  .animate().fadeIn(),
              const SizedBox(height: 16),
              Row(children: [
                Expanded(
                  child: AppTextField(
                      label: tr('first_name'),
                      controller: _firstCtrl,
                      validator: (v) => v!.isEmpty ? tr('required') : null),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: AppTextField(
                      label: tr('last_name'),
                      controller: _lastCtrl,
                      validator: (v) => v!.isEmpty ? tr('required') : null),
                ),
              ]).animate().fadeIn(delay: 100.ms),
              const SizedBox(height: 14),
              AppTextField(
                label: tr('phone'),
                controller: _phoneCtrl,
                prefixIcon: Icons.phone_outlined,
                keyboardType: TextInputType.phone,
              ).animate().fadeIn(delay: 150.ms),
              const SizedBox(height: 14),
              AppTextField(
                label: tr('address'),
                controller: _addressCtrl,
                prefixIcon: Icons.home_outlined,
                maxLines: 2,
              ).animate().fadeIn(delay: 200.ms),
              const SizedBox(height: 14),
              AppTextField(
                label: tr('city'),
                controller: _cityCtrl,
                prefixIcon: Icons.location_city_outlined,
              ).animate().fadeIn(delay: 250.ms),
              const SizedBox(height: 14),
              AppTextField(
                label: tr('zip_code'),
                controller: _zipCtrl,
                prefixIcon: Icons.markunread_mailbox_outlined,
                keyboardType: TextInputType.text,
              ).animate().fadeIn(delay: 275.ms),
              const SizedBox(height: 14),
              TextFormField(
                controller: _countryCtrl,
                readOnly: true,
                onTap: _pickCountry,
                style: TextStyle(color: colors.textPrimary, fontSize: 15),
                decoration: InputDecoration(
                  labelText: tr('country'),
                  prefixIcon: const Icon(Icons.flag_outlined, size: 20),
                  suffixIcon: const Icon(Icons.arrow_drop_down_rounded),
                ),
              ).animate().fadeIn(delay: 300.ms),
              const SizedBox(height: 28),
              AppButton(
                label: tr('save_changes'),
                isLoading: _saving,
                onTap: _save,
                icon: Icons.check_rounded,
              ).animate().fadeIn(delay: 350.ms),
              const SizedBox(height: 16),
              AppButton(
                label: tr('change_password'),
                outlined: true,
                icon: Icons.lock_outline,
                onTap: () => _showChangePassword(),
              ).animate().fadeIn(delay: 400.ms),
              const SizedBox(height: 40),
            ],
          ),
        ),
      ),
    );
  }

  Widget _balanceRow(user) {
    final colors = context.colors;
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [colors.bgCard, colors.surface],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: colors.primary.withValues(alpha: 0.2)),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: [
          _stat(context.tr('balance'), '${user.currencySymbol}${user.balance.toStringAsFixed(2)}',
              colors.primary),
          Container(width: 1, height: 40, color: colors.divider),
          _stat(context.tr('deposited'), '+${user.currencySymbol}${user.totalDeposit.toStringAsFixed(0)}',
              AppTheme.income),
          Container(width: 1, height: 40, color: colors.divider),
          _stat(context.tr('withdrawn'), '-${user.currencySymbol}${user.totalWithdraw.toStringAsFixed(0)}',
              AppTheme.expense),
        ],
      ),
    ).animate().fadeIn(delay: 50.ms);
  }

  Widget _stat(String label, String value, Color color) {
    final colors = context.colors;
    return Column(
      children: [
        Text(value,
            style: TextStyle(
                color: color, fontSize: 15, fontWeight: FontWeight.w700)),
        const SizedBox(height: 4),
        Text(label,
            style: TextStyle(color: colors.textSecondary, fontSize: 11)),
      ],
    );
  }

  void _showChangePassword() {
    final colors = context.colors;
    final currCtrl = TextEditingController();
    final newCtrl  = TextEditingController();
    final confCtrl = TextEditingController();
    final key = GlobalKey<FormState>();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: colors.bgCard,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (_) => Padding(
        padding: EdgeInsets.only(
          left: 24, right: 24, top: 24,
          bottom: MediaQuery.of(context).viewInsets.bottom + 24,
        ),
        child: Form(
          key: key,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Center(child: Container(width: 40, height: 4,
                  decoration: BoxDecoration(color: colors.divider,
                      borderRadius: BorderRadius.circular(2)))),
              const SizedBox(height: 16),
              Text(context.tr('change_password'),
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700,
                      color: colors.textPrimary)),
              const SizedBox(height: 20),
              AppTextField(label: context.tr('current_password'), controller: currCtrl,
                  obscure: true, validator: (v) => v!.isEmpty ? context.tr('required') : null),
              const SizedBox(height: 12),
              AppTextField(label: context.tr('new_password'), controller: newCtrl,
                  obscure: true, validator: (v) => v!.length < 8 ? context.tr('min_8_chars') : null),
              const SizedBox(height: 12),
              AppTextField(label: context.tr('confirm_password'), controller: confCtrl,
                  obscure: true, validator: (v) => v != newCtrl.text ? context.tr('mismatch') : null),
              const SizedBox(height: 20),
              AppButton(
                label: context.tr('update_password'),
                onTap: () async {
                  if (!key.currentState!.validate()) return;

                  final navigator = Navigator.of(context);
                  final messenger = ScaffoldMessenger.of(context);

                  try {
                    await AuthService.changePassword(
                      currentPassword: currCtrl.text,
                      newPassword: newCtrl.text,
                      confirmation: confCtrl.text,
                    );

                    if (!mounted) return;
                    navigator.pop();
                    messenger.showSnackBar(
                      SnackBar(
                        content: Text(context.tr('password_changed')),
                        backgroundColor: AppTheme.success,
                      ),
                    );
                  } catch (e) {
                    if (!mounted) return;
                    messenger.showSnackBar(
                      SnackBar(
                        content: Text(e.toString()),
                        backgroundColor: AppTheme.error,
                      ),
                    );
                  }
                },
              ),
            ],
          ),
        ),
      ),
    );
  }
}
