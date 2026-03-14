import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:qr_flutter/qr_flutter.dart';
import '../../config/app_colors.dart';
import '../../config/app_theme.dart';
import '../../l10n/app_localizations.dart';
import '../../models/virtual_card.dart';
import '../../services/card_service.dart';
import '../../widgets/card_widget.dart';
import '../../widgets/common_widgets.dart';

enum _DigitalDetailTab { transactions, deposits, points, addon }

class CardDetailScreen extends StatefulWidget {
  final VirtualCard card;
  const CardDetailScreen({super.key, required this.card});

  @override
  State<CardDetailScreen> createState() => _CardDetailScreenState();
}

class _CardDetailScreenState extends State<CardDetailScreen> {
  VirtualCard? _detail;
  List<dynamic> _transactions = [];
  List<dynamic> _deposits = [];
  List<dynamic> _points = [];
  List<VirtualCard> _addons = [];
  bool _loading = true;
  bool _loadingFunds = false;
  bool _loadingToggle = false;
  bool _loading3ds = false;
  bool _loadingAddonAction = false;
  String? _openingAddonCardId;
  _DigitalDetailTab _digitalTab = _DigitalDetailTab.transactions;
  final _amountCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadDetail();
  }

  @override
  void dispose() {
    _dismissKeyboard();
    _amountCtrl.dispose();
    super.dispose();
  }

  void _dismissKeyboard() {
    FocusManager.instance.primaryFocus?.unfocus();
  }

  Future<void> _loadDetail() async {
    setState(() => _loading = true);
    try {
      Map<String, dynamic> data;
      switch (widget.card.cardType) {
        case 'master':
          data = await CardService.getMasterCardDetail(widget.card.cardId);
          break;
        case 'visa':
          data = await CardService.getVisaCardDetail(widget.card.cardId);
          break;
        default:
          data = await CardService.getDigitalCardDetail(widget.card.cardId);
      }
      if (mounted) {
        setState(() {
          _detail = data['card'] as VirtualCard? ?? widget.card;
          _transactions = data['transactions'] as List? ?? [];
          _deposits = data['deposits'] as List? ?? [];
          _points = data['points'] as List? ?? [];
          _addons = (data['addons'] as List? ?? []).whereType<VirtualCard>().toList();
          if ((_detail?.isAddon ?? false) && _digitalTab == _DigitalDetailTab.addon) {
            _digitalTab = _DigitalDetailTab.transactions;
          }
          _loading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _loadFunds() async {
    final amount = double.tryParse(_amountCtrl.text);
    if (amount == null || amount <= 0) return;

    try {
      switch (widget.card.cardType) {
        case 'master':
          await CardService.masterLoadFunds(widget.card.cardId, amount);
          break;
        case 'visa':
          await CardService.visaLoadFunds(widget.card.cardId, amount);
          break;
        default:
          await CardService.digitalLoadFunds(widget.card.cardId, amount);
      }
      if (mounted) {
        Navigator.pop(context);
        _amountCtrl.clear();
        _showSnack(context.tr('funds_loaded_successfully'), isError: false);
        _loadDetail();
      }
    } catch (e) {
      if (mounted) _showSnack(e.toString(), isError: true);
    }
  }

  Future<void> _toggleCardStatus(VirtualCard card) async {
    if (_loadingToggle) return;
    setState(() => _loadingToggle = true);
    try {
      switch (card.cardType) {
        case 'master':
          if (card.isBlocked) {
            await CardService.masterUnblockCard(card.cardId);
          } else {
            await CardService.masterBlockCard(card.cardId);
          }
          break;
        case 'visa':
          if (card.isBlocked) {
            await CardService.visaUnblockCard(card.cardId);
          } else {
            await CardService.visaBlockCard(card.cardId);
          }
          break;
        default:
          if (card.isBlocked) {
            await CardService.digitalUnblockCard(card.cardId);
          } else {
            await CardService.digitalBlockCard(card.cardId);
          }
      }
      if (mounted) {
        _showSnack(
          card.isBlocked
              ? context.tr('card_unblocked_successfully')
              : context.tr('card_blocked_successfully'),
          isError: false,
        );
      }
      _loadDetail();
    } catch (e) {
      if (mounted) _showSnack(e.toString(), isError: true);
    } finally {
      if (mounted) setState(() => _loadingToggle = false);
    }
  }

  void _showSnack(String msg, {required bool isError}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(msg),
        backgroundColor: isError ? AppTheme.error : AppTheme.success,
      ),
    );
  }

  void _showCopyToast(String message) {
    final colors = context.colors;
    Fluttertoast.showToast(
      msg: message,
      toastLength: Toast.LENGTH_SHORT,
      gravity: ToastGravity.BOTTOM,
      backgroundColor: colors.bgCard,
      textColor: colors.textPrimary,
      fontSize: 13,
    );
  }

  void _showQrPreview(CardDepositAddress item) {
    showDialog(
      context: context,
      builder: (_) {
        final colors = context.colors;
        return Dialog(
          backgroundColor: Colors.transparent,
          insetPadding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
          child: Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: colors.bgCard,
              borderRadius: BorderRadius.circular(24),
              border: Border.all(color: colors.divider),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.28),
                  blurRadius: 28,
                  offset: const Offset(0, 16),
                ),
              ],
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                      decoration: BoxDecoration(
                        color: colors.primary.withValues(alpha: 0.14),
                        borderRadius: BorderRadius.circular(999),
                      ),
                      child: Text(
                        item.asset,
                        style: TextStyle(
                          color: colors.primary,
                          fontWeight: FontWeight.w700,
                          fontSize: 11,
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        item.network,
                        style: TextStyle(color: colors.textSecondary, fontSize: 13),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    IconButton(
                      onPressed: () => Navigator.pop(context),
                      icon: Icon(Icons.close, color: colors.textSecondary),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Container(
                  width: 240,
                  height: 240,
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: QrImageView(
                    data: item.address,
                    version: QrVersions.auto,
                    backgroundColor: Colors.white,
                  ),
                ),
                const SizedBox(height: 16),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: colors.surfaceLight,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: colors.divider),
                  ),
                  child: SelectableText(
                    item.address,
                    textAlign: TextAlign.center,
                    style: TextStyle(color: colors.textPrimary, fontSize: 13, height: 1.45),
                  ),
                ),
                const SizedBox(height: 14),
                SizedBox(
                  width: double.infinity,
                  child: AppButton(
                    label: context.tr('copy_address'),
                    icon: Icons.copy_rounded,
                    onTap: () async {
                      await Clipboard.setData(ClipboardData(text: item.address));
                      if (!mounted) return;
                      Navigator.pop(context);
                       _showCopyToast(context.tr('address_copied'));
                    },
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Future<void> _showLoadFundsSheet() async {
    if (mounted) setState(() => _loadingFunds = true);
    double? loadFee;
    try {
      final fees = await CardService.getCardFees();
      loadFee = (fees['bsiload_fee'] as num?)?.toDouble();
    } catch (_) {}

    if (!mounted) return;
    setState(() => _loadingFunds = false);

    showModalBottomSheet(
      context: context,
      backgroundColor: context.colors.bgCard,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (_) => StatefulBuilder(
        builder: (sheetCtx, setSheetState) {
          final colors = context.colors;
          bool sheetLoading = false;

          Future<void> doLoad() async {
            setSheetState(() => sheetLoading = true);
            await _loadFunds();
            if (sheetCtx.mounted) setSheetState(() => sheetLoading = false);
          }

          return Padding(
            padding: EdgeInsets.only(
              left: 24, right: 24, top: 24,
              bottom: MediaQuery.of(context).viewInsets.bottom + 24,
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: 40, height: 4,
                    decoration: BoxDecoration(
                      color: colors.divider,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                const SizedBox(height: 20),
                Text(context.tr('load_funds'),
                    style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w700,
                        color: colors.textPrimary)),
                const SizedBox(height: 6),
                Text('${context.tr('card')}: ${widget.card.maskedNumber}',
                    style: TextStyle(
                        color: colors.textSecondary, fontSize: 13)),
                const SizedBox(height: 10),
                if (widget.card.cardType == 'digital') ...[
                  Text(
                    context.tr('choose_deposit_rail_below'),
                    style: TextStyle(color: colors.textSecondary, fontSize: 13),
                  ),
                  const SizedBox(height: 14),
                  if ((_detail?.depositAddresses.isEmpty ?? true))
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: colors.surfaceLight,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: colors.divider),
                      ),
                      child: Text(
                        context.tr('no_deposit_addresses_available'),
                        style: TextStyle(color: colors.textSecondary, fontSize: 12),
                      ),
                    )
                  else
                    SizedBox(
                      height: MediaQuery.of(context).size.height * 0.62,
                      child: ListView.separated(
                        itemCount: _detail!.depositAddresses.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 12),
                        itemBuilder: (_, i) {
                          final dep = _detail!.depositAddresses[i];
                          return _DepositAddressTile(
                            item: dep,
                            onPreviewQr: () => _showQrPreview(dep),
                            onCopy: () async {
                              await Clipboard.setData(ClipboardData(text: dep.address));
                              if (mounted) {
                                _showCopyToast(context.tr('address_copied'));
                              }
                            },
                          );
                        },
                      ),
                    ),
                  const SizedBox(height: 16),
                  AppButton(label: context.tr('close'), outlined: true, onTap: () => Navigator.pop(context)),
                ] else ...[
                  Text(
                    '${context.tr('fund_loading_fee_of')} ${((loadFee ?? 0)).toStringAsFixed(2)}% ${context.tr('will_be_charged')}',
                    style: TextStyle(color: colors.textSecondary, fontSize: 13),
                  ),
                  const SizedBox(height: 16),
                  AppTextField(
                    label: context.tr('amount_usd'),
                    hint: '0.00',
                    controller: _amountCtrl,
                    prefixIcon: Icons.attach_money,
                    keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  ),
                  const SizedBox(height: 24),
                  AppButton(
                    label: context.tr('load_funds'),
                    isLoading: sheetLoading,
                    onTap: sheetLoading ? null : doLoad,
                  ),
                ],
              ],
            ),
          );
        },
      ),
    );
  }

  Future<void> _showAddonApplySheet(VirtualCard card) async {
    if (card.cardType != 'digital' || card.isAddon == true || _loadingAddonAction) {
      return;
    }

    setState(() => _loadingAddonAction = true);
    double digifee = 0;
    try {
      final fees = await CardService.getCardFees();
      digifee = (fees['digifee'] as num?)?.toDouble() ?? 0;
    } catch (_) {}

    if (!mounted) return;
    setState(() => _loadingAddonAction = false);

    bool submitting = false;
    await showModalBottomSheet(
      context: context,
      backgroundColor: context.colors.bgCard,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (sheetCtx) => StatefulBuilder(
        builder: (_, setSheetState) {
          final colors = context.colors;
          return Padding(
          padding: EdgeInsets.only(
            left: 24,
            right: 24,
            top: 20,
            bottom: MediaQuery.of(sheetCtx).viewInsets.bottom + 24,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 42,
                  height: 4,
                  decoration: BoxDecoration(
                    color: colors.divider,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 18),
              Text(
                context.tr('create_addon_card'),
                style: TextStyle(
                  color: colors.textPrimary,
                  fontSize: 18,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 12),
              Text(
                '${context.tr('addon_card_fee_of')} \$${digifee.toStringAsFixed(2)} ${context.tr('would_apply_continue')}',
                style: TextStyle(
                  color: colors.textSecondary,
                  fontSize: 13,
                  height: 1.45,
                ),
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: AppButton(
                      label: context.tr('cancel'),
                      outlined: true,
                      onTap: submitting ? null : () => Navigator.pop(sheetCtx),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: AppButton(
                      label: context.tr('continue'),
                      isLoading: submitting,
                      onTap: submitting
                          ? null
                          : () async {
                              setSheetState(() => submitting = true);
                              try {
                                final res = await CardService.applyAddonCard(card.cardId);
                                if (!mounted) return;
                                Navigator.pop(context);
                                _showSnack(
                                  (res['message'] ?? context.tr('addon_card_applied_successfully')).toString(),
                                  isError: false,
                                );
                                await _loadDetail();
                              } catch (e) {
                                _showSnack(e.toString(), isError: true);
                              } finally {
                                if (sheetCtx.mounted) {
                                  setSheetState(() => submitting = false);
                                }
                              }
                            },
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                decoration: BoxDecoration(
                  color: colors.primary.withValues(alpha: 0.08),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: colors.primary.withValues(alpha: 0.2)),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(Icons.info_outline, color: colors.primary, size: 16),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        context.tr('addon_cards_share_same_balance'),
                        style: TextStyle(
                          color: colors.textSecondary,
                          fontSize: 12,
                          height: 1.35,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        );}
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final card = _detail ?? widget.card;
    final isDigital = card.cardType == 'digital';
    final tr = context.tr;

    return PopScope(
      onPopInvokedWithResult: (_, __) => _dismissKeyboard(),
      child: Scaffold(
        backgroundColor: context.colors.bgDark,
        appBar: AppBar(
          title: Text('${card.cardType == 'visa' ? tr('visa') : card.cardType == 'master' ? tr('mastercard') : tr('digital')} ${tr('card')}'),
          actions: [
            if (card.cardType == 'digital' && card.isAddon != true)
              IconButton(
                icon: _loadingAddonAction
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Icon(Icons.add_circle_outline),
                onPressed: _loadingAddonAction ? null : () => _showAddonApplySheet(card),
                tooltip: tr('addon_card'),
              ),
            IconButton(
              icon: const Icon(Icons.more_vert),
              onPressed: _showCardMenu,
            ),
          ],
        ),
        body: RefreshIndicator(
          onRefresh: _loadDetail,
          color: context.colors.primary,
          backgroundColor: context.colors.bgCard,
          child: _loading
              ? Center(child: CircularProgressIndicator(color: context.colors.primary))
              : SingleChildScrollView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _FlippableDetailCard(card: card),
                      const SizedBox(height: 24),
                      _actionButtons(card),
                      const SizedBox(height: 28),
                      SectionHeader(title: isDigital ? tr('card_activity') : tr('card_transactions')),
                      const SizedBox(height: 12),
                      if (isDigital) ...[
                        _digitalTabBar(card),
                        const SizedBox(height: 12),
                        _digitalTabContent(card),
                      ] else
                        _transactionsList(),
                    ],
                  ),
                ),
        ),
      ),
    );
  }

  Widget _actionButtons(VirtualCard card) {
    final items = <Widget>[
      _ActionBtn(
        icon: Icons.account_balance_wallet_outlined,
        label: card.balance != null ? '\$${card.balance!.toStringAsFixed(2)}' : '\$0.00',
        color: AppTheme.success,
        onTap: () {},
      ),
      _ActionBtn(
        icon: Icons.add_rounded,
        label: context.tr('load_funds'),
        color: AppTheme.primary,
        isLoading: _loadingFunds,
        onTap: _loadingFunds ? () {} : () => _showLoadFundsSheet(),
      ),
      _ActionBtn(
        icon: card.isBlocked ? Icons.lock_open : Icons.lock,
        label: card.isBlocked ? context.tr('unblock') : context.tr('block'),
        color: card.isBlocked ? AppTheme.success : AppTheme.error,
        isLoading: _loadingToggle,
        onTap: _loadingToggle ? () {} : () => _toggleCardStatus(card),
      ),
      if (card.cardType == 'digital')
        _ActionBtn(
          icon: Icons.security,
          label: context.tr('three_ds'),
          color: const Color(0xFF7C4DFF),
          isLoading: _loading3ds,
          onTap: _loading3ds ? () {} : _check3ds,
        ),
    ];

    return Row(
      children: [
        for (int i = 0; i < items.length; i++) ...[
          Expanded(child: items[i]),
          if (i < items.length - 1) const SizedBox(width: 8),
        ],
      ],
    );
  }

  Widget _transactionsList() {
    if (_transactions.isEmpty) {
      return EmptyState(
        icon: Icons.receipt_long_outlined,
        title: context.tr('no_card_transactions'),
        subtitle: context.tr('card_transactions_will_appear'),
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
        itemCount: _transactions.length,
        separatorBuilder: (_, __) =>
            Divider(height: 1, indent: 74, color: context.colors.divider),
        itemBuilder: (_, i) {
          final t = _txMap(_transactions[i]);
          final type = (t['type'] ?? t['card_transaction_type'] ?? '')
              .toString()
              .toLowerCase();
          final isCredit = [
            'credit',
            'deposit',
            'refund',
            'reversal',
            'load',
            'topup',
          ].contains(type);
          final status = (t['status'] ?? '').toString().toLowerCase();
          final narrative =
              (t['narrative'] ??
                      t['description'] ??
                      (t['merchant'] is Map<String, dynamic>
                          ? t['merchant']['name']
                          : null) ??
                      context.tr('card_transaction'))
                  .toString();
          final method =
              (t['method'] ??
                      t['transaction_type'] ??
                      t['transactionType'] ??
                      t['type'] ??
                      '')
                  .toString();
          final reference = (t['reference'] ??
                  t['id'] ??
                  t['bridgecard_transaction_reference'] ??
                  t['client_transaction_reference'] ??
                  t['transactionHash'] ??
                  '')
              .toString();
          final createdAt =
              (t['paymentDateTime'] ??
                      t['createdAt'] ??
                      t['created_at'] ??
                      t['updatedAt'] ??
                      t['transaction_date'] ??
                      '')
                  .toString();
          final amount = _txAmount(t);
          final currency = ((t['currency'] ?? 'usd').toString()).toUpperCase();
          final merchantLogo = _txMerchantLogo(t);
          final category = _txCategory(t);

          final amountColor = isCredit ? AppTheme.income : AppTheme.expense;
          final signedAmount = '${isCredit ? '+' : '-'}$currency ${amount.toStringAsFixed(2)}';
          final statusText = [
            if (status.isNotEmpty) status[0].toUpperCase() + status.substring(1),
            if ((t['declineReason'] ?? '').toString().isNotEmpty)
              (t['declineReason'] ?? '').toString(),
          ].join(' • ');

          return ListTile(
            contentPadding:
                const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            leading: Container(
              width: 40,
              height: 40,
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: amountColor.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(10),
              ),
              child: merchantLogo != null
                  ? ClipRRect(
                      borderRadius: BorderRadius.circular(6),
                      child: Image.network(
                        merchantLogo,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => Icon(
                          isCredit
                              ? Icons.south_west_rounded
                              : Icons.north_east_rounded,
                          color: amountColor,
                          size: 18,
                        ),
                      ),
                    )
                  : Icon(
                      isCredit
                          ? Icons.south_west_rounded
                          : Icons.north_east_rounded,
                      color: amountColor,
                      size: 18,
                    ),
            ),
            title: Text(
              narrative,
              style: TextStyle(
                  color: context.colors.textPrimary,
                  fontSize: 13,
                  fontWeight: FontWeight.w600),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
            subtitle: Text(
              [
                if (method.isNotEmpty) method,
                if (category.isNotEmpty) category,
                if (statusText.isNotEmpty) statusText,
                if (createdAt.isNotEmpty) _formatTxDate(createdAt),
                if (reference.isNotEmpty) '#$reference',
              ].join(' • '),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(color: context.colors.textSecondary, fontSize: 11.5),
            ),
            trailing: Text(
              signedAmount,
              style: TextStyle(
                  color: amountColor,
                  fontWeight: FontWeight.w700,
                  fontSize: 14),
            ),
          );
        },
      ),
    ).animate().fadeIn(delay: 300.ms);
  }

  Map<String, dynamic> _txMap(dynamic raw) {
    if (raw is Map<String, dynamic>) return raw;
    return <String, dynamic>{};
  }

  double _txAmount(Map<String, dynamic> t) {
    final centRaw = t['centAmount'];
    if (centRaw != null) {
      final cents = double.tryParse(centRaw.toString());
      if (cents != null) return cents / 100;
    }
    final amount = _readAmount(t, 'amount');
    if (amount != null && amount != 0) {
      // BridgeCard Mastercard history can provide whole-number minor units in `amount`.
      if (t.containsKey('card_transaction_type') && !t.containsKey('centAmount')) {
        return amount.abs() / 100;
      }

      return amount.abs();
    }

    // Digital payloads can carry usable value in merchant/original amount.
    for (final key in ['merchantAmount', 'originalAmount', 'originalMerchantAmount']) {
      final fallback = _readAmount(t, key);
      if (fallback != null && fallback != 0) {
        return fallback.abs();
      }
    }

    if (amount != null) return amount.abs();
    return 0;
  }

  double? _readAmount(Map<String, dynamic> t, String key) {
    final raw = t[key];
    if (raw == null) return null;
    if (raw is num) return raw.toDouble();
    return double.tryParse(raw.toString());
  }

  Widget _digitalTabBar(VirtualCard card) {
    final tabs = <_DigitalDetailTab>[
      _DigitalDetailTab.transactions,
      _DigitalDetailTab.deposits,
      _DigitalDetailTab.points,
      if (card.isAddon != true) _DigitalDetailTab.addon,
    ];

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: [
          for (final tab in tabs) ...[
            _DigitalTabChip(
              label: _digitalTabLabel(tab),
              selected: _digitalTab == tab,
              onTap: () => setState(() => _digitalTab = tab),
            ),
            if (tab != tabs.last) const SizedBox(width: 10),
          ],
        ],
      ),
    );
  }

  Widget _digitalTabContent(VirtualCard card) {
    switch (_digitalTab) {
      case _DigitalDetailTab.deposits:
        return _depositsList();
      case _DigitalDetailTab.points:
        return _pointsList();
      case _DigitalDetailTab.addon:
        if (card.isAddon == true) {
          return const SizedBox.shrink();
        }
        return _addonList();
      case _DigitalDetailTab.transactions:
        return _transactionsList();
    }
  }

  String _digitalTabLabel(_DigitalDetailTab tab) {
    switch (tab) {
      case _DigitalDetailTab.transactions:
        return context.tr('transactions');
      case _DigitalDetailTab.deposits:
        return context.tr('deposits');
      case _DigitalDetailTab.points:
        return context.tr('points');
      case _DigitalDetailTab.addon:
        return context.tr('addon');
    }
  }

  Widget _depositsList() {
    if (_deposits.isEmpty) {
      return EmptyState(
        icon: Icons.account_balance_wallet_outlined,
        title: context.tr('no_deposits'),
        subtitle: context.tr('deposits_for_card_will_appear'),
      );
    }

    return _sectionContainer(
      ListView.separated(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        itemCount: _deposits.length,
        separatorBuilder: (_, __) =>
            Divider(height: 1, indent: 72, color: context.colors.divider),
        itemBuilder: (_, i) {
          final deposit = _txMap(_deposits[i]);
          final hash = (deposit['transactionHash'] ?? '').toString();
          final amount = _depositAmount(deposit);

          return ListTile(
            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            leading: Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: AppTheme.success.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.south_west_rounded, color: AppTheme.success, size: 18),
            ),
            title: Text(
              context.tr('deposit'),
              style: TextStyle(
                color: context.colors.textPrimary,
                fontSize: 13,
                fontWeight: FontWeight.w600,
              ),
            ),
            subtitle: Text(
              [
                if ((deposit['createdAt'] ?? '').toString().isNotEmpty)
                  _formatTxDate((deposit['createdAt'] ?? '').toString()),
                if (hash.isNotEmpty) _truncateMiddle(hash),
              ].join(' • '),
              style: TextStyle(color: context.colors.textSecondary, fontSize: 11.5),
            ),
            trailing: Text(
              '+USDC ${amount.toStringAsFixed(2)}',
              style: const TextStyle(
                color: AppTheme.success,
                fontWeight: FontWeight.w700,
                fontSize: 14,
              ),
            ),
          );
        },
      ),
    ).animate().fadeIn(delay: 300.ms);
  }

  Widget _pointsList() {
    if (_points.isEmpty) {
      return EmptyState(
        icon: Icons.stars_rounded,
        title: context.tr('no_points'),
        subtitle: context.tr('points_activity_will_appear'),
      );
    }

    return _sectionContainer(
      ListView.separated(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        itemCount: _points.length,
        separatorBuilder: (_, __) =>
            Divider(height: 1, indent: 72, color: context.colors.divider),
        itemBuilder: (_, i) {
          final point = _txMap(_points[i]);
          final type = (point['type'] ?? '').toString();
          final isDebit = type == '-';
          final color = isDebit ? AppTheme.error : AppTheme.success;
          final points = (point['points'] ?? '').toString();
          final balance = (point['balance'] ?? '').toString();

          return ListTile(
            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            leading: Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(
                isDebit ? Icons.remove_rounded : Icons.add_rounded,
                color: color,
                size: 18,
              ),
            ),
            title: Text(
              (point['details'] ?? context.tr('points_activity')).toString(),
              style: TextStyle(
                color: context.colors.textPrimary,
                fontSize: 13,
                fontWeight: FontWeight.w600,
              ),
            ),
            subtitle: Text(
              [
                if ((point['created_at'] ?? '').toString().isNotEmpty)
                  _formatTxDate((point['created_at'] ?? '').toString()),
                if (balance.isNotEmpty) '${context.tr('balance')} $balance',
              ].join(' • '),
              style: TextStyle(color: context.colors.textSecondary, fontSize: 11.5),
            ),
            trailing: Text(
              '${isDebit ? '-' : '+'}$points',
              style: TextStyle(
                color: color,
                fontWeight: FontWeight.w700,
                fontSize: 14,
              ),
            ),
          );
        },
      ),
    ).animate().fadeIn(delay: 300.ms);
  }

  Widget _addonList() {
    if (_addons.isEmpty) {
      return EmptyState(
        icon: Icons.credit_card,
        title: context.tr('no_addon_cards'),
        subtitle: context.tr('addon_cards_linked_will_appear'),
      );
    }

    return _sectionContainer(
      ListView.separated(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        itemCount: _addons.length,
        separatorBuilder: (_, __) =>
            Divider(height: 1, indent: 72, color: context.colors.divider),
        itemBuilder: (_, i) {
          final addon = _addons[i];
          return ListTile(
            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            leading: Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: context.colors.primary.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(Icons.credit_card, color: context.colors.primary, size: 18),
            ),
            title: Text(
              addon.cardHolder?.isNotEmpty == true ? addon.cardHolder! : addon.cardId,
              style: TextStyle(
                color: context.colors.textPrimary,
                fontSize: 13,
                fontWeight: FontWeight.w600,
              ),
            ),
            subtitle: Text(
              addon.lastFour?.isNotEmpty == true
                  ? '**** ${addon.lastFour}'
                  : addon.maskedNumber,
              style: TextStyle(color: context.colors.textSecondary, fontSize: 11.5),
            ),
            trailing: _openingAddonCardId == addon.cardId
                ? SizedBox(
                    width: 18,
                    height: 18,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      color: context.colors.textSecondary,
                    ),
                  )
                : Icon(Icons.chevron_right_rounded, color: context.colors.textSecondary),
            onTap: _openingAddonCardId != null ? null : () => _openAddonCard(addon),
          );
        },
      ),
    ).animate().fadeIn(delay: 300.ms);
  }

  Widget _sectionContainer(Widget child) {
    return Container(
      decoration: BoxDecoration(
        color: context.colors.bgCard,
        borderRadius: BorderRadius.circular(16),
      ),
      child: child,
    );
  }

  Future<void> _openAddonCard(VirtualCard addon) async {
    setState(() => _openingAddonCardId = addon.cardId);
    await Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => CardDetailScreen(card: addon)),
    );
    if (mounted) {
      setState(() => _openingAddonCardId = null);
      await _loadDetail();
    }
  }

  double _depositAmount(Map<String, dynamic> deposit) {
    final raw = deposit['amount'];
    final amount = double.tryParse((raw ?? 0).toString()) ?? 0;
    return amount >= 1000000 ? amount / 1000000 : amount;
  }

  String _truncateMiddle(String value, {int edge = 8}) {
    if (value.length <= edge * 2 + 3) return value;
    return '${value.substring(0, edge)}...${value.substring(value.length - edge)}';
  }

  String? _txMerchantLogo(Map<String, dynamic> t) {
    final enriched = t['enriched_data'];
    if (enriched is Map<String, dynamic>) {
      final logo = enriched['merchant_logo']?.toString();
      if (logo != null && logo.isNotEmpty) return logo;
    }
    return null;
  }

  String _txCategory(Map<String, dynamic> t) {
    final enriched = t['enriched_data'];
    if (enriched is Map<String, dynamic>) {
      return (enriched['transaction_category'] ?? '').toString();
    }
    return '';
  }

  String _formatTxDate(String raw) {
    try {
      final dt = DateTime.parse(raw).toLocal();
      final mm = dt.month.toString().padLeft(2, '0');
      final dd = dt.day.toString().padLeft(2, '0');
      final hh = dt.hour.toString().padLeft(2, '0');
      final min = dt.minute.toString().padLeft(2, '0');
      return '$mm/$dd/${dt.year} $hh:$min';
    } catch (_) {
      return raw;
    }
  }

  Future<void> _check3ds() async {
    if (_loading3ds) return;
    setState(() => _loading3ds = true);
    try {
      final data = await CardService.check3ds(widget.card.cardId);
      if (!mounted) return;

      final status = (data['status'] ?? '').toString().toLowerCase();
      final code = (data['code'] ?? '').toString();
      final payload = data['data'];
      final hasPending = status != 'failure' &&
          code != '422' &&
          payload is Map<String, dynamic> &&
          payload.isNotEmpty;

      if (!hasPending) {
        _showCopyToast(context.tr('no_transaction_for_approval'));
        return;
      }

      await _show3dsDecisionSheet(payload);
    } catch (e) {
      _showSnack(e.toString(), isError: true);
    } finally {
      if (mounted) setState(() => _loading3ds = false);
    }
  }

  Future<void> _show3dsDecisionSheet(Map<String, dynamic> payload) async {
    final eventId = (payload['eventId'] ?? payload['eventid'] ?? payload['eventTargetId'] ?? '').toString();
    final merchantName = (payload['merchantName'] ?? context.tr('unknown_merchant')).toString();
    final merchantAmount = (payload['merchantAmount'] ?? '').toString();
    final merchantCurrency = (payload['merchantCurrency'] ?? '').toString();
    final maskedPan = (payload['maskedPan'] ?? '').toString();

    bool approving = false;

    await showModalBottomSheet(
      context: context,
      backgroundColor: AppTheme.bgCard,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (sheetCtx) => StatefulBuilder(
        builder: (_, setSheetState) => Padding(
          padding: EdgeInsets.only(
            left: 24,
            right: 24,
            top: 20,
            bottom: MediaQuery.of(sheetCtx).viewInsets.bottom + 24,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 42,
                  height: 4,
                  decoration: BoxDecoration(
                    color: context.colors.divider,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 18),
              Text(
                context.tr('approval_3ds_required'),
                style: TextStyle(
                  color: context.colors.textPrimary,
                  fontSize: 18,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 12),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: context.colors.surfaceLight,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: context.colors.divider),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      merchantName,
                      style: TextStyle(
                        color: context.colors.textPrimary,
                        fontSize: 15,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      '$merchantCurrency $merchantAmount',
                      style: TextStyle(
                        color: context.colors.primary,
                        fontSize: 15,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    if (maskedPan.isNotEmpty) ...[
                      const SizedBox(height: 6),
                      Text(
                        '${context.tr('card')}: $maskedPan',
                        style: TextStyle(
                          color: context.colors.textSecondary,
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ],
                ),
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: AppButton(
                      label: context.tr('reject'),
                      outlined: true,
                      onTap: approving ? null : () => Navigator.pop(sheetCtx),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: AppButton(
                      label: context.tr('approve'),
                      isLoading: approving,
                      onTap: approving || eventId.isEmpty
                          ? null
                          : () async {
                              setSheetState(() => approving = true);
                              try {
                                await CardService.approve3ds(
                                  cardId: widget.card.cardId,
                                  eventId: eventId,
                                );
                                if (!mounted) return;
                                Navigator.pop(context);
                                _showSnack(context.tr('approval_3ds_success'), isError: false);
                              } catch (e) {
                                _showSnack(e.toString(), isError: true);
                              } finally {
                                if (sheetCtx.mounted) {
                                  setSheetState(() => approving = false);
                                }
                              }
                            },
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showCardMenu() {
    showModalBottomSheet(
      context: context,
      backgroundColor: context.colors.bgCard,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (_) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const SizedBox(height: 8),
            Container(width: 40, height: 4,
                decoration: BoxDecoration(
                    color: context.colors.divider,
                    borderRadius: BorderRadius.circular(2))),
            const SizedBox(height: 16),
            ListTile(
              leading: Icon(Icons.copy, color: context.colors.primary),
              title: Text(context.tr('copy_card_number'),
                  style: TextStyle(color: context.colors.textPrimary)),
              onTap: () {
                final card = _detail ?? widget.card;
                final text = card.cardNumber?.trim() ?? '';
                Navigator.pop(context);
                if (text.isEmpty) {
                  _showSnack(context.tr('card_number_unavailable'), isError: true);
                  return;
                }
                Clipboard.setData(ClipboardData(text: text));
                _showSnack(context.tr('card_number_copied'), isError: false);
              },
            ),
            if (widget.card.cardType == 'digital')
              ListTile(
                leading: Icon(Icons.qr_code, color: context.colors.primary),
                title: Text(context.tr('wallet_otp'),
                    style: TextStyle(color: context.colors.textPrimary)),
                onTap: () async {
                  Navigator.pop(context);
                  final data = await CardService.getWalletOtp(widget.card.cardId);
                  if (!mounted) return;
                  final otp = data['data']?['activationCode'];
                  _showSnack(
                    otp != null ? '${context.tr('wallet_otp')}: $otp' : context.tr('no_otp_available_yet'),
                    isError: false,
                  );
                },
              ),
            const SizedBox(height: 8),
          ],
        ),
      ),
    );
  }

}

class _DepositAddressTile extends StatelessWidget {
  final CardDepositAddress item;
  final VoidCallback onPreviewQr;
  final VoidCallback onCopy;

  const _DepositAddressTile({required this.item, required this.onPreviewQr, required this.onCopy});

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [colors.surfaceLight, colors.bgCard.withValues(alpha: 0.98)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: colors.divider),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.08),
            blurRadius: 16,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              GestureDetector(
                onTap: onPreviewQr,
                child: Container(
                  width: 92,
                  height: 92,
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(14),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.06),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Stack(
                    children: [
                      Positioned.fill(
                        child: QrImageView(
                          data: item.address,
                          version: QrVersions.auto,
                          backgroundColor: Colors.white,
                          eyeStyle: const QrEyeStyle(eyeShape: QrEyeShape.square, color: Colors.black),
                          dataModuleStyle: const QrDataModuleStyle(dataModuleShape: QrDataModuleShape.square, color: Colors.black),
                        ),
                      ),
                      Positioned(
                        right: 0,
                        bottom: 0,
                        child: Container(
                          padding: const EdgeInsets.all(4),
                          decoration: BoxDecoration(
                            color: colors.primary,
                            borderRadius: BorderRadius.circular(999),
                          ),
                          child: const Icon(Icons.open_in_full_rounded, color: Colors.white, size: 12),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: colors.primary.withValues(alpha: 0.14),
                            borderRadius: BorderRadius.circular(999),
                          ),
                          child: Text(
                            item.asset,
                            style: TextStyle(color: colors.primary, fontSize: 11, fontWeight: FontWeight.w700),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            item.network,
                            style: TextStyle(color: colors.textSecondary, fontSize: 12, fontWeight: FontWeight.w500),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Text(
                      context.tr('wallet_address'),
                      style: TextStyle(
                        color: colors.textSecondary.withValues(alpha: 0.9),
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        letterSpacing: 0.3,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: colors.bgDark.withValues(alpha: 0.32),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: colors.divider.withValues(alpha: 0.7)),
                      ),
                      child: SelectableText(
                        item.address,
                        style: TextStyle(color: colors.textPrimary, fontSize: 12.5, height: 1.4),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: onPreviewQr,
                  icon: const Icon(Icons.qr_code_2_rounded, size: 16),
                  label: Text(context.tr('enlarge_qr')),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: colors.textPrimary,
                    side: BorderSide(color: colors.divider.withValues(alpha: 0.7)),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: onCopy,
                  icon: const Icon(Icons.copy_rounded, size: 16),
                  label: Text(context.tr('copy_address')),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: colors.primary,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _DigitalTabChip extends StatelessWidget {
  final String label;
  final bool selected;
  final VoidCallback onTap;

  const _DigitalTabChip({
    required this.label,
    required this.selected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    return InkWell(
      borderRadius: BorderRadius.circular(999),
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        decoration: BoxDecoration(
          color: selected ? colors.primary : colors.bgCard,
          borderRadius: BorderRadius.circular(999),
          border: Border.all(
            color: selected ? colors.primary : colors.divider,
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: selected ? Colors.white : colors.textSecondary,
            fontSize: 12,
            fontWeight: FontWeight.w700,
          ),
        ),
      ),
    );
  }
}

class _ActionBtn extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;
  final bool isLoading;

  const _ActionBtn({
    required this.icon,
    required this.label,
    required this.color,
    required this.onTap,
    this.isLoading = false,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: isLoading ? null : onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.12),
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: color.withValues(alpha: 0.3)),
        ),
        child: Column(
          children: [
            SizedBox(
              width: 22,
              height: 22,
              child: isLoading
                  ? CircularProgressIndicator(strokeWidth: 2.5, color: color)
                  : Icon(icon, color: color, size: 22),
            ),
            const SizedBox(height: 6),
            Text(label,
                style: TextStyle(
                    color: color,
                    fontSize: 12,
                    fontWeight: FontWeight.w600)),
          ],
        ),
      ),
    ).animate().fadeIn(duration: 300.ms);
  }
}

class _FlippableDetailCard extends StatefulWidget {
  final VirtualCard card;

  const _FlippableDetailCard({required this.card});

  @override
  State<_FlippableDetailCard> createState() => _FlippableDetailCardState();
}

class _FlippableDetailCardState extends State<_FlippableDetailCard> {
  bool _showBack = false;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        GestureDetector(
          onTap: () => setState(() => _showBack = !_showBack),
          child: AnimatedSwitcher(
            duration: const Duration(milliseconds: 320),
            switchInCurve: Curves.easeOut,
            switchOutCurve: Curves.easeIn,
            transitionBuilder: (child, animation) {
              return ScaleTransition(scale: animation, child: child);
            },
            child: _showBack
                ? _CardBack(key: const ValueKey('back'), card: widget.card)
                : CardWidget(
                    key: const ValueKey('front'),
                    card: widget.card,
                    showFullNumber: true,
                  ),
          ),
        ),
        const SizedBox(height: 8),
        Text(
          context.tr('tap_card_to_flip'),
          style: TextStyle(color: context.colors.textSecondary, fontSize: 12),
        ),
      ],
    );
  }
}

class _CardBack extends StatelessWidget {
  final VirtualCard card;

  const _CardBack({super.key, required this.card});

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 200,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(20),
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [card.cardGradientStart, card.cardGradientEnd],
        ),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: double.infinity,
              height: 38,
              color: Colors.black.withValues(alpha: 0.75),
            ),
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: Container(
                    height: 34,
                      color: Colors.white.withValues(alpha: 0.92),
                    alignment: Alignment.centerRight,
                    padding: const EdgeInsets.symmetric(horizontal: 10),
                    child: Text(
                      card.cvv?.isNotEmpty == true ? card.cvv! : '---',
                      style: const TextStyle(
                        color: Colors.black87,
                        fontWeight: FontWeight.w700,
                        fontSize: 16,
                        letterSpacing: 2,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Text(
                  context.tr('cvv'),
                  style: const TextStyle(color: Colors.white70, fontSize: 11),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              context.tr('billing_address').toUpperCase(),
              style: const TextStyle(
                color: Colors.white70,
                fontSize: 10,
                fontWeight: FontWeight.w700,
                letterSpacing: 1,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              card.formattedBillingAddress,
              maxLines: 3,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 12,
                height: 1.35,
              ),
            ),
          ],
        ),
      ),
    ).animate().fadeIn(duration: 220.ms);
  }
}



