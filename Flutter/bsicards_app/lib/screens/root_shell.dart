import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../l10n/app_localizations.dart';
import '../config/app_colors.dart';
import '../providers/auth_provider.dart';
import 'cards/cards_home_screen.dart';
import 'dashboard/dashboard_screen.dart';
import 'deposit/deposit_screen.dart';
import 'notifications/notifications_screen.dart';
import 'transactions/transactions_screen.dart';

class RootShell extends StatefulWidget {
  const RootShell({super.key});

  @override
  State<RootShell> createState() => _RootShellState();
}

class _RootShellState extends State<RootShell> with WidgetsBindingObserver {
  int _index = 0;
  bool _lockOnResume = false;
  bool _locked = false;
  bool _unlocking = false;
  final List<int> _tabReloadVersion = [0, 0, 0, 0];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _applyFullscreenMode();
  }

  Future<void> _applyFullscreenMode() async {
    await SystemChrome.setEnabledSystemUIMode(SystemUiMode.immersiveSticky);
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.paused || state == AppLifecycleState.inactive) {
      _lockOnResume = true;
      return;
    }
    if (state == AppLifecycleState.resumed) {
      _applyFullscreenMode();
      _promptBiometricOnResume();
    }
  }

  Future<void> _promptBiometricOnResume() async {
    if (!_lockOnResume || _unlocking || !mounted) return;

    final auth = context.read<AuthProvider>();
    if (!auth.isAuthenticated) {
      _lockOnResume = false;
      return;
    }

    final enabled = await auth.isBiometricEnabled();
    if (!enabled) {
      _lockOnResume = false;
      return;
    }

    if (!mounted) return;
    setState(() {
      _locked = true;
      _unlocking = true;
    });

    final approved = await auth.authenticateForAppUnlock();

    if (!mounted) return;
    setState(() {
      _unlocking = false;
      _locked = !approved;
      if (approved) {
        _lockOnResume = false;
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final tr = context.tr;

    return Stack(
      children: [
        Scaffold(
          body: _buildPage(_index),
          floatingActionButton: _index == 0
              ? FloatingActionButton(
                  backgroundColor: context.colors.primary,
                  child: const Icon(Icons.notifications_outlined),
                  onPressed: () {
                    Navigator.of(context).push(
                      MaterialPageRoute(builder: (_) => const NotificationsScreen()),
                    );
                  },
                )
              : null,
          bottomNavigationBar: BottomNavigationBar(
            currentIndex: _index,
            onTap: (v) {
              setState(() {
                _index = v;
                _tabReloadVersion[v] = _tabReloadVersion[v] + 1;
              });
            },
            items: [
              BottomNavigationBarItem(
                icon: const Icon(Icons.dashboard_outlined),
                activeIcon: const Icon(Icons.dashboard),
                label: tr('home'),
              ),
              BottomNavigationBarItem(
                icon: const Icon(Icons.credit_card_outlined),
                activeIcon: const Icon(Icons.credit_card),
                label: tr('cards'),
              ),
              BottomNavigationBarItem(
                icon: const Icon(Icons.add_circle_outline),
                activeIcon: const Icon(Icons.add_circle),
                label: tr('deposit'),
              ),
              BottomNavigationBarItem(
                icon: const Icon(Icons.receipt_long_outlined),
                activeIcon: const Icon(Icons.receipt_long),
                label: tr('transactions'),
              ),
            ],
          ),
        ),
        if (_locked)
          Positioned.fill(
            child: Container(
              color: context.colors.bgDark.withValues(alpha: 0.96),
              child: Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.fingerprint, size: 46, color: context.colors.primary),
                    const SizedBox(height: 12),
                    Text(
                      tr('unlock_with_biometrics'),
                      style: TextStyle(
                        color: context.colors.textPrimary,
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    const SizedBox(height: 18),
                    ElevatedButton.icon(
                      onPressed: _unlocking ? null : _promptBiometricOnResume,
                      icon: const Icon(Icons.lock_open_rounded),
                      label: Text(_unlocking ? tr('authenticating') : tr('try_again')),
                    ),
                  ],
                ),
              ),
            ),
          ),
      ],
    );
  }

  Widget _buildPage(int index) {
    final version = _tabReloadVersion[index];
    switch (index) {
      case 0:
        return KeyedSubtree(
          key: ValueKey('tab-0-v$version'),
          child: const DashboardScreen(),
        );
      case 1:
        return KeyedSubtree(
          key: ValueKey('tab-1-v$version'),
          child: const CardsHomeScreen(),
        );
      case 2:
        return KeyedSubtree(
          key: ValueKey('tab-2-v$version'),
          child: const DepositScreen(),
        );
      case 3:
        return KeyedSubtree(
          key: ValueKey('tab-3-v$version'),
          child: const TransactionsScreen(),
        );
      default:
        return KeyedSubtree(
          key: ValueKey('tab-0-v$version'),
          child: const DashboardScreen(),
        );
    }
  }
}

