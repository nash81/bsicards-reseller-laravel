import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';

import 'config/app_colors.dart';
import 'config/app_config.dart';
import 'l10n/app_localizations.dart';
import 'providers/auth_provider.dart';
import 'providers/locale_provider.dart';
import 'providers/theme_provider.dart';
import 'screens/auth/login_screen.dart';
import 'screens/root_shell.dart';
import 'services/app_snackbar.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  final localeProvider = LocaleProvider();
  await localeProvider.load();

  final themeProvider = ThemeProvider();
  await themeProvider.load();

  if (!kIsWeb && Platform.isAndroid) {
    await SystemChrome.setEnabledSystemUIMode(SystemUiMode.immersiveSticky);
  }

  // Allow self-signed certificates for localhost/LAN API hosts.
  HttpOverrides.global = _DevHttpOverrides();

  runApp(BsiCardsApp(localeProvider: localeProvider, themeProvider: themeProvider));
}

class BsiCardsApp extends StatelessWidget {
  final LocaleProvider localeProvider;
  final ThemeProvider themeProvider;

  const BsiCardsApp({
    super.key,
    required this.localeProvider,
    required this.themeProvider,
  });

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider<LocaleProvider>.value(value: localeProvider),
        ChangeNotifierProvider<ThemeProvider>.value(value: themeProvider),
      ],
      child: Consumer2<LocaleProvider, ThemeProvider>(
        builder: (_, localeState, themeState, __) => MaterialApp(
          onGenerateTitle: (context) => context.tr('app_name'),
          debugShowCheckedModeBanner: false,
          scaffoldMessengerKey: AppSnackbar.messengerKey,
          theme: themeState.themeData,
          locale: localeState.locale,
          supportedLocales: AppLocalizations.supportedLocales,
          localizationsDelegates: const [
            AppLocalizations.delegate,
            GlobalMaterialLocalizations.delegate,
            GlobalWidgetsLocalizations.delegate,
            GlobalCupertinoLocalizations.delegate,
          ],
          home: const _BootScreen(),
        ),
      ),
    );
  }
}

class _BootScreen extends StatefulWidget {
  const _BootScreen();

  @override
  State<_BootScreen> createState() => _BootScreenState();
}

class _BootScreenState extends State<_BootScreen> {
  bool _checking = true;

  @override
  void initState() {
    super.initState();
    _bootstrap();
  }

  Future<void> _bootstrap() async {
    final auth = context.read<AuthProvider>();
    await Future.delayed(const Duration(milliseconds: 900));
    final loggedIn = await auth.checkAuth();
    if (!loggedIn) {
      await auth.tryBiometricLogin();
    }
    if (mounted) {
      setState(() => _checking = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_checking) {
      return const _SplashView();
    }

    return Consumer<AuthProvider>(
      builder: (_, auth, __) {
        if (auth.isAuthenticated) {
          return const RootShell();
        }
        return const LoginScreen();
      },
    );
  }
}

class _SplashView extends StatelessWidget {
  const _SplashView();

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    return Scaffold(
      backgroundColor: colors.bgDark,
      body: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 86,
              height: 86,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [colors.primary, colors.primaryDark],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(24),
              ),
              child: const Icon(Icons.credit_card_rounded, color: Colors.white, size: 42),
            )
                .animate(onPlay: (c) => c.repeat(reverse: true))
                .scale(begin: const Offset(1, 1), end: const Offset(1.06, 1.06), duration: 900.ms),
            const SizedBox(height: 20),
            Text(
              'BSI Cards',
              style: TextStyle(
                color: colors.textPrimary,
                fontSize: 28,
                fontWeight: FontWeight.w800,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              'Modern Banking, Simplified',
              style: TextStyle(color: colors.textSecondary),
            ),
            const SizedBox(height: 20),
            CircularProgressIndicator(color: colors.primary),
          ],
        ),
      ),
    );
  }
}

class _DevHttpOverrides extends HttpOverrides {
  static final Set<String> _allowedHosts = {
    'localhost',
    '127.0.0.1',
    _extractHost(AppConfig.baseUrl),
  };

  @override
  HttpClient createHttpClient(SecurityContext? context) {
    final client = super.createHttpClient(context);
    client.badCertificateCallback = (X509Certificate cert, String host, int port) {
      return _allowedHosts.contains(host);
    };
    return client;
  }

  static String _extractHost(String url) {
    try {
      return Uri.parse(url).host;
    } catch (_) {
      return '';
    }
  }
}
