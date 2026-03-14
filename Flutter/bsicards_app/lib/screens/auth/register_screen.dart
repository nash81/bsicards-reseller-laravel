import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../../config/app_theme.dart';
import '../../l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../root_shell.dart';
import '../../widgets/common_widgets.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _firstCtrl  = TextEditingController();
  final _lastCtrl   = TextEditingController();
  final _emailCtrl  = TextEditingController();
  final _phoneCtrl  = TextEditingController();
  final _passCtrl   = TextEditingController();
  final _confCtrl   = TextEditingController();

  @override
  void dispose() {
    for (final c in [_firstCtrl,_lastCtrl,_emailCtrl,_phoneCtrl,_passCtrl,_confCtrl]) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _register() async {
    if (!_formKey.currentState!.validate()) return;
    final auth = context.read<AuthProvider>();
    final ok = await auth.register(
      firstName: _firstCtrl.text.trim(),
      lastName: _lastCtrl.text.trim(),
      email: _emailCtrl.text.trim(),
      password: _passCtrl.text,
      confirmation: _confCtrl.text,
      phone: _phoneCtrl.text.trim(),
    );
    if (ok && mounted) {
      final messenger = ScaffoldMessenger.of(context);
      messenger.hideCurrentSnackBar();
      messenger.showSnackBar(
        const SnackBar(
          content: Text('Registration successful.'),
          backgroundColor: AppTheme.success,
          duration: Duration(milliseconds: 900),
        ),
      );
      await Future.delayed(const Duration(milliseconds: 700));
      if (!mounted) return;
      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const RootShell()),
        (_) => false,
      );
      return;
    }
    if (!ok && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(auth.error ?? context.tr('registration_failed')),
            backgroundColor: AppTheme.error),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final tr = context.tr;
    return Scaffold(
      backgroundColor: AppTheme.bgDark,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new, size: 18),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const SizedBox(height: 10),
                Text(tr('create_account'),
                    style: TextStyle(
                        fontSize: 26,
                        fontWeight: FontWeight.w800,
                        color: AppTheme.textPrimary))
                    .animate().fadeIn().slideX(begin: -0.2),
                const SizedBox(height: 8),
                Text(tr('fill_details_to_get_started'),
                    style: TextStyle(fontSize: 14, color: AppTheme.textSecondary))
                    .animate().fadeIn(delay: 100.ms),
                const SizedBox(height: 32),
                Row(children: [
                  Expanded(
                    child: AppTextField(
                      label: tr('first_name'),
                      controller: _firstCtrl,
                      prefixIcon: Icons.person_outline,
                      validator: (v) => v!.isEmpty ? tr('required') : null,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: AppTextField(
                      label: tr('last_name'),
                      controller: _lastCtrl,
                      validator: (v) => v!.isEmpty ? tr('required') : null,
                    ),
                  ),
                ]).animate().fadeIn(delay: 200.ms).slideY(begin: 0.1),
                const SizedBox(height: 16),
                AppTextField(
                  label: tr('email'),
                  hint: 'you@example.com',
                  controller: _emailCtrl,
                  prefixIcon: Icons.email_outlined,
                  keyboardType: TextInputType.emailAddress,
                  validator: (v) => v!.isEmpty ? tr('enter_email') : null,
                ).animate().fadeIn(delay: 250.ms).slideY(begin: 0.1),
                const SizedBox(height: 16),
                AppTextField(
                  label: tr('phone_optional'),
                  hint: '+1 234 567 8900',
                  controller: _phoneCtrl,
                  prefixIcon: Icons.phone_outlined,
                  keyboardType: TextInputType.phone,
                ).animate().fadeIn(delay: 300.ms).slideY(begin: 0.1),
                const SizedBox(height: 16),
                AppTextField(
                  label: tr('password'),
                  controller: _passCtrl,
                  prefixIcon: Icons.lock_outline,
                  obscure: true,
                  validator: (v) =>
                      v!.length < 8 ? tr('min_8_characters') : null,
                ).animate().fadeIn(delay: 350.ms).slideY(begin: 0.1),
                const SizedBox(height: 16),
                AppTextField(
                  label: tr('confirm_password'),
                  controller: _confCtrl,
                  prefixIcon: Icons.lock_outline,
                  obscure: true,
                  validator: (v) =>
                      v != _passCtrl.text ? tr('passwords_do_not_match') : null,
                ).animate().fadeIn(delay: 400.ms).slideY(begin: 0.1),
                const SizedBox(height: 32),
                Consumer<AuthProvider>(
                  builder: (_, auth, __) => AppButton(
                    label: tr('create_account'),
                    isLoading: auth.isLoading,
                    onTap: _register,
                  ).animate().fadeIn(delay: 450.ms),
                ),
                const SizedBox(height: 32),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(tr('already_have_account'),
                        style: const TextStyle(color: AppTheme.textSecondary)),
                    TextButton(
                      onPressed: () => Navigator.pop(context),
                      child: Text(tr('sign_in'),
                          style: const TextStyle(fontWeight: FontWeight.w700)),
                    ),
                  ],
                ).animate().fadeIn(delay: 500.ms),
                const SizedBox(height: 40),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

