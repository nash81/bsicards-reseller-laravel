import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../../config/app_theme.dart';
import '../../l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/common_widgets.dart';
import 'register_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  bool _enableBiometric = false;
  bool _biometricReady = false;
  bool _biometricSupported = false;

  @override
  void initState() {
    super.initState();
    _loadBiometricState();
  }

  Future<void> _loadBiometricState() async {
    final auth = context.read<AuthProvider>();
    final supported = await auth.isBiometricSupported();
    final ready = await auth.hasBiometricCredentials();
    if (!mounted) return;
    setState(() {
      _biometricSupported = supported;
      _biometricReady = ready;
      _enableBiometric = ready;
    });
  }

  @override
  void dispose() {
    _emailCtrl.dispose();
    _passCtrl.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    if (!_formKey.currentState!.validate()) return;
    final auth = context.read<AuthProvider>();
    final email = _emailCtrl.text.trim();
    final password = _passCtrl.text;
    final ok = await auth.login(email, password);
    if (ok) {
      if (_enableBiometric) {
        await auth.enableBiometricLogin(email: email, password: password);
      } else {
        await auth.disableBiometricLogin();
      }
      if (mounted) {
        setState(() => _biometricReady = _enableBiometric);
      }
      return;
    }
    if (!ok && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(auth.error ?? context.tr('login_failed')), backgroundColor: AppTheme.error),
      );
    }
  }

  Future<void> _loginWithBiometric() async {
    final auth = context.read<AuthProvider>();
    final ok = await auth.tryBiometricLogin();
    if (!ok && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(context.tr('biometric_login_failed')),
          backgroundColor: AppTheme.error,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final tr = context.tr;
    return Scaffold(
      backgroundColor: AppTheme.bgDark,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const SizedBox(height: 60),
                // Logo / brand
                Center(
                  child: Column(
                    children: [
                      Container(
                        width: 80,
                        height: 80,
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [AppTheme.primary, AppTheme.primaryDark],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                          borderRadius: BorderRadius.circular(22),
                          boxShadow: [
                            BoxShadow(
                              color: AppTheme.primary.withValues(alpha: 0.4),
                              blurRadius: 20,
                              offset: const Offset(0, 8),
                            ),
                          ],
                        ),
                        child: const Icon(Icons.credit_card_rounded,
                            color: Colors.white, size: 40),
                      )
                          .animate()
                          .scale(duration: 500.ms, curve: Curves.elasticOut),
                      const SizedBox(height: 20),
                      Text(tr('app_name'),
                          style: TextStyle(
                              fontSize: 28,
                              fontWeight: FontWeight.w800,
                              color: AppTheme.textPrimary))
                          .animate()
                          .fadeIn(delay: 200.ms),
                      const SizedBox(height: 8),
                      Text(tr('app_tagline'),
                          style: TextStyle(
                              fontSize: 14, color: AppTheme.textSecondary))
                          .animate()
                          .fadeIn(delay: 300.ms),
                    ],
                  ),
                ),
                const SizedBox(height: 48),
                Text(tr('sign_in'),
                    style: TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.w700,
                        color: AppTheme.textPrimary))
                    .animate()
                    .fadeIn(delay: 400.ms)
                    .slideX(begin: -0.2),
                const SizedBox(height: 6),
                Text(tr('welcome_back_enter_credentials'),
                    style: TextStyle(
                        fontSize: 14, color: AppTheme.textSecondary))
                    .animate()
                    .fadeIn(delay: 450.ms),
                const SizedBox(height: 32),
                AppTextField(
                  label: tr('email_address'),
                  hint: 'you@example.com',
                  controller: _emailCtrl,
                  prefixIcon: Icons.email_outlined,
                  keyboardType: TextInputType.emailAddress,
                  validator: (v) =>
                      v == null || v.isEmpty ? tr('enter_email') : null,
                ).animate().fadeIn(delay: 500.ms).slideY(begin: 0.1),
                const SizedBox(height: 16),
                AppTextField(
                  label: tr('password'),
                  hint: '••••••••',
                  controller: _passCtrl,
                  prefixIcon: Icons.lock_outline,
                  obscure: true,
                  validator: (v) =>
                      v == null || v.isEmpty ? tr('enter_password') : null,
                ).animate().fadeIn(delay: 550.ms).slideY(begin: 0.1),
                const SizedBox(height: 10),
                Align(
                  alignment: Alignment.centerRight,
                  child: TextButton(
                    onPressed: () {},
                    child: Text(tr('forgot_password')),
                  ),
                ).animate().fadeIn(delay: 600.ms),
                const SizedBox(height: 24),
                if (_biometricSupported)
                  Row(
                    children: [
                      Checkbox(
                        value: _enableBiometric,
                        onChanged: (v) => setState(() => _enableBiometric = v ?? false),
                      ),
                      Expanded(
                        child: Text(
                          tr('enable_biometric_on_device'),
                          style: const TextStyle(color: AppTheme.textSecondary),
                        ),
                      ),
                    ],
                  ).animate().fadeIn(delay: 630.ms),
                const SizedBox(height: 8),
                Consumer<AuthProvider>(
                  builder: (_, auth, __) => AppButton(
                    label: tr('sign_in'),
                    isLoading: auth.isLoading,
                    onTap: _login,
                  ).animate().fadeIn(delay: 650.ms),
                ),
                if (_biometricReady) ...[
                  const SizedBox(height: 12),
                  Consumer<AuthProvider>(
                    builder: (_, auth, __) => AppButton(
                      label: tr('sign_in_biometric'),
                      outlined: true,
                      icon: Icons.fingerprint,
                      isLoading: auth.isLoading,
                      onTap: _loginWithBiometric,
                    ).animate().fadeIn(delay: 670.ms),
                  ),
                ],
                const SizedBox(height: 32),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(tr('no_account_question'),
                        style: const TextStyle(color: AppTheme.textSecondary)),
                    TextButton(
                      onPressed: () => Navigator.push(context,
                          MaterialPageRoute(
                              builder: (_) => const RegisterScreen())),
                      child: Text(tr('sign_up'),
                          style: const TextStyle(fontWeight: FontWeight.w700)),
                    ),
                  ],
                ).animate().fadeIn(delay: 700.ms),
                const SizedBox(height: 40),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

