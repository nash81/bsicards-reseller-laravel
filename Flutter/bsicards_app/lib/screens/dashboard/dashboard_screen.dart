import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../../config/app_colors.dart';
import '../../config/app_theme.dart';
import '../../l10n/app_localizations.dart';
import '../../models/transaction.dart';
import '../../providers/auth_provider.dart';
import '../../providers/locale_provider.dart';
import '../../providers/theme_provider.dart';
import '../../widgets/common_widgets.dart';
import '../../widgets/transaction_item.dart';
import '../cards/cards_home_screen.dart';
import '../deposit/deposit_screen.dart';
import '../profile/profile_screen.dart';
import '../transactions/transactions_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  List<Transaction> _recent = [];
  bool _loadingTxns = true;
  bool _balanceVisible = true;
  bool _biometricSupported = false;
  bool _biometricEnabled = false;
  bool _biometricBusy = false;
  String? _txnError;
  String? _loadingAction; // tracks which quick-action button is busy

  @override
  void initState() {
    super.initState();
    _loadData();
    _loadBiometricSettings();
  }

  Future<void> _loadBiometricSettings() async {
    final auth = context.read<AuthProvider>();
    final supported = await auth.isBiometricSupported();
    final enabled = await auth.canUseBiometricLogin();
    if (!mounted) return;
    setState(() {
      _biometricSupported = supported;
      _biometricEnabled = enabled;
    });
  }

  Future<String?> _askCurrentPassword() async {
    final tr = context.tr;
    final colors = context.colors;
    var password = '';
    final result = await showDialog<String>(
      context: context,
      builder: (dialogCtx) => AlertDialog(
        backgroundColor: colors.bgCard,
        title: Text(tr('enable_biometric_login'), style: TextStyle(color: colors.textPrimary)),
        content: TextField(
          obscureText: true,
          onChanged: (value) => password = value,
          style: TextStyle(color: colors.textPrimary),
          decoration: InputDecoration(
            labelText: tr('current_password'),
            hintText: tr('enter_your_password'),
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogCtx), child: Text(tr('cancel'))),
          TextButton(onPressed: () => Navigator.pop(dialogCtx, password), child: Text(tr('confirm'))),
        ],
      ),
    );
    return result;
  }

  Future<void> _toggleBiometric(
    bool enabled,
    BuildContext sheetBodyCtx,
    void Function(void Function()) setModal,
  ) async {
    if (_biometricBusy) return;
    final auth = context.read<AuthProvider>();
    final user = auth.user;
    if (user == null) return;

    void refreshSheet() {
      if (!mounted || !sheetBodyCtx.mounted) return;
      setModal(() {});
    }

    setState(() => _biometricBusy = true);
    refreshSheet();
    try {
      if (enabled) {
        final password = await _askCurrentPassword();
        if ((password ?? '').isEmpty) {
          if (mounted) {
            setState(() => _biometricBusy = false);
            refreshSheet();
          }
          return;
        }
        await auth.enableBiometricLogin(email: user.email, password: password!.trim());
        if (mounted) {
          setState(() => _biometricEnabled = true);
          refreshSheet();
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(context.tr('biometric_login_enabled')), backgroundColor: AppSemanticColors.success),
          );
        }
      } else {
        await auth.disableBiometricLogin();
        if (mounted) {
          setState(() => _biometricEnabled = false);
          refreshSheet();
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(context.tr('biometric_login_disabled')), backgroundColor: AppSemanticColors.success),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppSemanticColors.error),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _biometricBusy = false);
        refreshSheet();
      }
    }
  }

  Future<void> _showSettingsSheet() async {
    await showModalBottomSheet(
      context: context,
      backgroundColor: context.colors.bgCard,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (sheetCtx) => StatefulBuilder(
        builder: (ctx, setModal) {
          final tr = context.tr;
          final colors = context.colors;
          final localeProvider = context.read<LocaleProvider>();
          final themeProvider = context.read<ThemeProvider>();
          final selectedLanguage = AppLocalizations.languageOptionForCode(
            localeProvider.locale.languageCode,
          );

          return SafeArea(
            child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const SizedBox(height: 8),
              Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(color: colors.divider, borderRadius: BorderRadius.circular(2)),
              ),
              const SizedBox(height: 14),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: Align(
                  alignment: Alignment.centerLeft,
                  child: Text(
                    tr('settings'),
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: colors.textPrimary),
                  ),
                ),
              ),
              const SizedBox(height: 6),
              SwitchListTile.adaptive(
                value: _biometricEnabled,
                onChanged: (!_biometricSupported || _biometricBusy)
                    ? null
                    : (v) => _toggleBiometric(v, ctx, setModal),
                title: Text(tr('biometric_login'), style: TextStyle(color: colors.textPrimary)),
                subtitle: Text(
                  !_biometricSupported
                      ? tr('biometric_unavailable')
                      : tr('biometric_subtitle'),
                  style: TextStyle(color: colors.textSecondary, fontSize: 12),
                ),
                secondary: Icon(Icons.fingerprint, color: colors.primary),
              ),
              ListTile(
                leading: Icon(Icons.language_rounded, color: colors.primary),
                title: Text(tr('language'), style: TextStyle(color: colors.textPrimary)),
                subtitle: Text(
                  '${selectedLanguage.flag} ${selectedLanguage.name}',
                  style: TextStyle(color: colors.textSecondary, fontSize: 12),
                ),
                trailing: Icon(Icons.chevron_right_rounded, color: colors.textSecondary),
                onTap: () => _showLanguageSelector(sheetCtx, setModal),
              ),
              ListTile(
                leading: Icon(Icons.palette_rounded, color: colors.primary),
                title: Text(tr('theme'), style: TextStyle(color: colors.textPrimary)),
                subtitle: Text(
                  themeProvider.themeLabel,
                  style: TextStyle(color: colors.textSecondary, fontSize: 12),
                ),
                trailing: Icon(Icons.chevron_right_rounded, color: colors.textSecondary),
                onTap: () => _showThemeSelector(sheetCtx, setModal),
              ),
              ListTile(
                leading: Icon(Icons.logout_rounded, color: AppSemanticColors.error),
                title: Text(tr('logout'), style: const TextStyle(color: AppSemanticColors.error, fontWeight: FontWeight.w600)),
                onTap: () async {
                  Navigator.pop(sheetCtx);
                  await context.read<AuthProvider>().logout();
                },
              ),
              const SizedBox(height: 10),
            ],
          ),
        );
        },
      ),
    );
  }

  Future<void> _showLanguageSelector(
    BuildContext sheetCtx,
    StateSetter setSettingsModal,
  ) async {
    final tr = context.tr;
    final colors = context.colors;

    await showModalBottomSheet(
      context: sheetCtx,
      backgroundColor: colors.bgCard,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (pickerCtx) {
        final selectedCode = context.read<LocaleProvider>().locale.languageCode;

        return SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const SizedBox(height: 8),
              Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: colors.divider,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(height: 14),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: Align(
                  alignment: Alignment.centerLeft,
                  child: Text(
                    tr('select_language'),
                    style: TextStyle(
                      color: colors.textPrimary,
                      fontSize: 17,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 8),
              Flexible(
                child: ListView.separated(
                  shrinkWrap: true,
                  itemCount: AppLocalizations.languageOptions.length,
                  separatorBuilder: (_, __) => Divider(height: 1, color: colors.divider),
                  itemBuilder: (_, i) {
                    final option = AppLocalizations.languageOptions[i];
                    final isSelected = selectedCode == option.locale.languageCode;
                    return ListTile(
                      leading: Text(option.flag, style: const TextStyle(fontSize: 20)),
                      title: Text(option.name, style: TextStyle(color: colors.textPrimary)),
                      trailing: isSelected
                          ? Icon(Icons.check_circle, color: colors.primary)
                          : null,
                      onTap: () async {
                        await context.read<LocaleProvider>().setLocale(option.locale);
                        if (!pickerCtx.mounted) return;
                        Navigator.pop(pickerCtx);
                        if (!sheetCtx.mounted) return;
                        setSettingsModal(() {});
                      },
                    );
                  },
                ),
              ),
              const SizedBox(height: 10),
            ],
          ),
        );
      },
    );
  }

  Future<void> _showThemeSelector(
    BuildContext sheetCtx,
    StateSetter setSettingsModal,
  ) async {
    final tr = context.tr;
    final colors = context.colors;

    await showModalBottomSheet(
      context: sheetCtx,
      backgroundColor: colors.bgCard,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (pickerCtx) {
        final themeProvider = context.read<ThemeProvider>();

        final themes = [
          _ThemeOption(
            name: AppThemeName.oceanTribe,
            label: tr('theme_ocean_tribe'),
            description: tr('theme_ocean_tribe_desc'),
            accent: AppColors.oceanTribe.primary,
            bg: AppColors.oceanTribe.bgCard,
          ),
          _ThemeOption(
            name: AppThemeName.blackTiger,
            label: tr('theme_black_tiger'),
            description: tr('theme_black_tiger_desc'),
            accent: AppColors.blackTiger.primary,
            bg: AppColors.blackTiger.bgCard,
          ),
          _ThemeOption(
            name: AppThemeName.mysteriousElegance,
            label: tr('theme_mysterious_elegance'),
            description: tr('theme_mysterious_elegance_desc'),
            accent: AppColors.mysteriousElegance.primary,
            bg: AppColors.mysteriousElegance.bgCard,
          ),
        ];

        return SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const SizedBox(height: 8),
              Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: colors.divider,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(height: 14),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: Align(
                  alignment: Alignment.centerLeft,
                  child: Text(
                    tr('select_theme'),
                    style: TextStyle(
                      color: colors.textPrimary,
                      fontSize: 17,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 12),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Column(
                  children: themes.map((option) {
                    final isSelected = themeProvider.themeName == option.name;
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 10),
                      child: InkWell(
                        borderRadius: BorderRadius.circular(16),
                        onTap: () async {
                          await context.read<ThemeProvider>().setTheme(option.name);
                          if (!pickerCtx.mounted) return;
                          Navigator.pop(pickerCtx);
                          if (!sheetCtx.mounted) return;
                          setSettingsModal(() {});
                        },
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                          decoration: BoxDecoration(
                            color: option.bg,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(
                              color: isSelected ? option.accent : colors.divider,
                              width: isSelected ? 2 : 1,
                            ),
                          ),
                          child: Row(
                            children: [
                              // colour swatch preview
                              Container(
                                width: 42,
                                height: 42,
                                decoration: BoxDecoration(
                                  gradient: LinearGradient(
                                    colors: [option.accent, option.accent.withValues(alpha: 0.4)],
                                    begin: Alignment.topLeft,
                                    end: Alignment.bottomRight,
                                  ),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Icon(
                                  option.name == AppThemeName.blackTiger
                                      ? Icons.local_fire_department_rounded
                                      : option.name == AppThemeName.mysteriousElegance
                                          ? Icons.auto_awesome_rounded
                                          : Icons.waves_rounded,
                                  color: Colors.white,
                                  size: 20,
                                ),
                              ),
                              const SizedBox(width: 14),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      option.label,
                                      style: TextStyle(
                                        color: colors.textPrimary,
                                        fontSize: 15,
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                    const SizedBox(height: 2),
                                    Text(
                                      option.description,
                                      style: TextStyle(
                                        color: colors.textSecondary,
                                        fontSize: 12,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              if (isSelected)
                                Icon(Icons.check_circle_rounded,
                                    color: option.accent, size: 22),
                            ],
                          ),
                        ),
                      ),
                    );
                  }).toList(),
                ),
              ),
              const SizedBox(height: 12),
            ],
          ),
        );
      },
    );
  }

  Future<void> _loadData() async {
    final auth = context.read<AuthProvider>();
    await auth.refreshUser();
    setState(() { _loadingTxns = true; _txnError = null; });
    try {
      final txns = await auth.getRecentTransactions();
      if (mounted) setState(() { _recent = txns; _loadingTxns = false; });
    } catch (e) {
      debugPrint('⚠️ Recent transactions error: $e');
      if (mounted) setState(() { _loadingTxns = false; _txnError = e.toString(); });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: context.colors.bgDark,
      appBar: _buildAppBar(),
      body: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ── Fixed: balance card + quick actions + section header ──
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 14, 20, 0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                IntrinsicHeight(
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Expanded(flex: 57, child: _balanceCard()),
                      const SizedBox(width: 12),
                      Expanded(flex: 43, child: _quickActionsColumn()),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
                SectionHeader(
                  title: context.tr('recent_transactions'),
                  action: context.tr('see_all'),
                  onAction: () => Navigator.push(context,
                      MaterialPageRoute(
                          builder: (_) => const TransactionsScreen())),
                ),
                const SizedBox(height: 12),
              ],
            ),
          ),
          // ── Scrollable: transactions list only ────────────────────
          Expanded(
            child: RefreshIndicator(
              onRefresh: _loadData,
              color: context.colors.primary,
              backgroundColor: context.colors.bgCard,
              child: ListView(
                padding: const EdgeInsets.fromLTRB(20, 0, 20, 100),
                physics: const AlwaysScrollableScrollPhysics(),
                children: [_recentTransactions()],
              ),
            ),
          ),
        ],
      ),
    );
  }

  AppBar _buildAppBar() {
    return AppBar(
      backgroundColor: context.colors.bgCard,
      elevation: 0,
      titleSpacing: 20,
      automaticallyImplyLeading: false,
      title: Consumer<AuthProvider>(
        builder: (_, auth, __) => Row(
          children: [
            GestureDetector(
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const ProfileScreen()),
              ),
              child: CircleAvatar(
                radius: 20,
                backgroundColor: context.colors.primary.withValues(alpha: 0.2),
                backgroundImage: auth.user?.avatar != null
                    ? NetworkImage(auth.user!.avatar!)
                    : null,
                child: auth.user?.avatar == null
                    ? Text(
                        (auth.user?.firstName ?? 'U').substring(0, 1).toUpperCase(),
                        style: TextStyle(
                            color: context.colors.primary, fontWeight: FontWeight.bold),
                      )
                    : null,
              ),
            ),
            const SizedBox(width: 12),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(context.tr('welcome_back'),
                    style: TextStyle(fontSize: 12, color: context.colors.textSecondary)),
                Text(
                  auth.user?.firstName ?? '...',
                  style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                      color: context.colors.textPrimary),
                ),
              ],
            ),
          ],
        ),
      ),
      actions: [
        IconButton(
          onPressed: _showSettingsSheet,
          icon: const Icon(Icons.settings_outlined),
          tooltip: context.tr('settings'),
        ),
        IconButton(
          onPressed: () => Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const ProfileScreen()),
          ),
          icon: const Icon(Icons.person_outline),
          tooltip: context.tr('profile'),
        ),
      ],
    );
  }

  Widget _balanceCard() {
    final colors = context.colors;
    return Consumer<AuthProvider>(
      builder: (_, auth, __) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [
              colors.bgCard,
              colors.surface,
            ],
          ),
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: colors.primary.withValues(alpha: 0.2)),
          boxShadow: [
            BoxShadow(
              color: colors.primary.withValues(alpha: 0.15),
              blurRadius: 18,
              offset: const Offset(0, 6),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(context.tr('total_balance'),
                    style: TextStyle(
                        fontSize: 11,
                        color: colors.textSecondary,
                        fontWeight: FontWeight.w500)),
                GestureDetector(
                  onTap: () => setState(() => _balanceVisible = !_balanceVisible),
                  child: Icon(
                    _balanceVisible ? Icons.visibility : Icons.visibility_off,
                    color: colors.textSecondary,
                    size: 16,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 6),
            AnimatedSwitcher(
              duration: const Duration(milliseconds: 300),
              child: _balanceVisible
                  ? Text(
                      '${auth.user?.currencySymbol ?? '\$'}${(auth.user?.balance ?? 0).toStringAsFixed(2)}',
                      key: const ValueKey('visible'),
                      style: TextStyle(
                          fontSize: 22,
                          fontWeight: FontWeight.w800,
                          color: colors.textPrimary,
                          letterSpacing: -0.5),
                    )
                  : Text('••••••',
                      key: const ValueKey('hidden'),
                      style: TextStyle(
                          fontSize: 22,
                          fontWeight: FontWeight.w800,
                          color: colors.textSecondary)),
            ),
          ],
        ),
      ).animate().fadeIn(duration: 500.ms).slideY(begin: 0.1),
    );
  }

  Widget _quickActionsColumn() {
    final actions = [
      _ActionItem(
        icon: Icons.add_rounded,
        label: context.tr('add_money'),
        color: context.colors.primary,
        onTap: () => Navigator.push(context,
            MaterialPageRoute(builder: (_) => const DepositScreen())),
      ),
      _ActionItem(
        icon: Icons.credit_card_rounded,
        label: context.tr('cards'),
        color: const Color(0xFF7C4DFF),
        onTap: () => Navigator.push(context,
            MaterialPageRoute(builder: (_) => const CardsHomeScreen())),
      ),
      _ActionItem(
        icon: Icons.history_rounded,
        label: context.tr('transactions'),
        color: const Color(0xFF00BCD4),
        onTap: () => Navigator.push(context,
            MaterialPageRoute(builder: (_) => const TransactionsScreen())),
      ),
    ];

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        for (int i = 0; i < actions.length; i++) ...[
          _buildActionRow(actions[i], i),
          if (i < actions.length - 1) const SizedBox(height: 8),
        ],
      ],
    );
  }

  Widget _buildActionRow(_ActionItem item, int index) {
    final isBusy = _loadingAction == item.label;
    return GestureDetector(
      onTap: isBusy
          ? null
          : () async {
              setState(() => _loadingAction = item.label);
              await item.onTap();
              if (mounted) setState(() => _loadingAction = null);
            },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 9),
        decoration: BoxDecoration(
          color: item.color.withValues(alpha: isBusy ? 0.06 : 0.1),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: item.color.withValues(alpha: 0.25)),
        ),
        child: Row(
          children: [
            SizedBox(
              width: 18,
              height: 18,
              child: isBusy
                  ? CircularProgressIndicator(
                      strokeWidth: 2,
                      color: item.color,
                    )
                  : Icon(item.icon, color: item.color, size: 18),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                item.label,
                style: TextStyle(
                  fontSize: 12,
                  color: item.color,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
            Icon(Icons.chevron_right_rounded,
                color: item.color.withValues(alpha: 0.5), size: 16),
          ],
        ),
      ),
    )
        .animate(delay: Duration(milliseconds: 80 * index))
        .fadeIn()
        .slideX(begin: 0.1);
  }

  Widget _recentTransactions() {
    if (_loadingTxns) {
      return Column(
        children: List.generate(
          4,
          (i) => const Padding(
            padding: EdgeInsets.only(bottom: 12),
            child: Row(
              children: [
                ShimmerBox(height: 44, width: 44, radius: 12),
                SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      ShimmerBox(height: 14, width: 180),
                      SizedBox(height: 6),
                      ShimmerBox(height: 11, width: 100),
                    ],
                  ),
                ),
                ShimmerBox(height: 16, width: 70),
              ],
            ),
          ),
        ),
      );
    }

    if (_recent.isEmpty) {
      if (_txnError != null) {
        return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: context.colors.bgCard,
          borderRadius: BorderRadius.circular(16),
        ),
        child: Column(
          children: [
            const Icon(Icons.error_outline, color: Colors.redAccent, size: 32),
            const SizedBox(height: 8),
            Text(
              _txnError!,
              style: const TextStyle(color: Colors.redAccent, fontSize: 12),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 10),
            TextButton.icon(
              onPressed: _loadData,
              icon: const Icon(Icons.refresh, size: 16),
              label: Text(context.tr('retry')),
            ),
          ],
        ),
        );
      }
      return EmptyState(
        icon: Icons.receipt_long_outlined,
        title: context.tr('no_transactions_yet'),
        subtitle: context.tr('recent_transactions_will_appear'),
      );
    }

    return Container(
      decoration: BoxDecoration(
        color: context.colors.bgCard,
        borderRadius: BorderRadius.circular(16),
      ),
      child: ListView.separated(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        itemCount: _recent.length,
        separatorBuilder: (_, __) => Divider(
            height: 1, indent: 74, color: context.colors.divider),
        itemBuilder: (_, i) => TransactionItem(transaction: _recent[i]),
      ),
    );
  }
}

class _ActionItem {
  final IconData icon;
  final String label;
  final Color color;
  final Future<void> Function() onTap;
  _ActionItem({required this.icon, required this.label, required this.color, required this.onTap});
}

class _ThemeOption {
  final AppThemeName name;
  final String label;
  final String description;
  final Color accent;
  final Color bg;
  const _ThemeOption({
    required this.name,
    required this.label,
    required this.description,
    required this.accent,
    required this.bg,
  });
}

