import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import '../../config/app_colors.dart';
import '../../config/app_theme.dart';
import '../../l10n/app_localizations.dart';
import '../../models/transaction.dart';
import '../../services/transaction_service.dart';
import '../../widgets/common_widgets.dart';
import '../../widgets/transaction_item.dart';

class TransactionsScreen extends StatefulWidget {
  const TransactionsScreen({super.key});

  @override
  State<TransactionsScreen> createState() => _TransactionsScreenState();
}

class _TransactionsScreenState extends State<TransactionsScreen> {
  final _searchCtrl = TextEditingController();
  final _scrollCtrl = ScrollController();

  List<Transaction> _items = [];
  TransactionMeta? _meta;
  bool _loading = true;
  bool _loadingMore = false;
  int _page = 1;
  String? _typeFilter;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadTransactions();
    _scrollCtrl.addListener(_onScroll);
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    _scrollCtrl.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollCtrl.position.pixels >= _scrollCtrl.position.maxScrollExtent - 100) {
      if (!_loadingMore && _meta != null && _page < _meta!.lastPage) {
        _loadMore();
      }
    }
  }

  Future<void> _loadTransactions({bool reset = true}) async {
    if (reset) {
      setState(() { _loading = true; _page = 1; _items = []; _error = null; });
    }
    try {
      final result = await TransactionService.getTransactions(
        page: _page,
        type: _typeFilter,
        search: _searchCtrl.text.isEmpty ? null : _searchCtrl.text,
      );
      if (mounted) {
        setState(() {
          _items = result['transactions'] as List<Transaction>;
          _meta  = result['meta'] as TransactionMeta?;
          _loading = false;
        });
      }
    } catch (e) {
      debugPrint('⚠️ Transactions load error: $e');
      if (mounted) setState(() { _loading = false; _error = e.toString(); });
    }
  }

  Future<void> _loadMore() async {
    setState(() { _loadingMore = true; _page++; });
    try {
      final result = await TransactionService.getTransactions(
        page: _page,
        type: _typeFilter,
        search: _searchCtrl.text.isEmpty ? null : _searchCtrl.text,
      );
      if (mounted) {
        setState(() {
          _items.addAll(result['transactions'] as List<Transaction>);
          _meta = result['meta'] as TransactionMeta?;
          _loadingMore = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() { _loadingMore = false; _page--; });
    }
  }

  @override
  Widget build(BuildContext context) {
    final tr = context.tr;
    final colors = context.colors;
    final types = [
      {'label': tr('all'), 'value': null},
      {'label': tr('deposits'), 'value': 'deposit'},
      {'label': tr('withdraw'), 'value': 'withdraw'},
      {'label': tr('cards'), 'value': 'subtract'},
      {'label': tr('bonus'), 'value': 'signup_bonus'},
    ];

    return Scaffold(
      backgroundColor: colors.bgDark,
      appBar: AppBar(title: Text(tr('transactions'))),
      body: Column(
        children: [
          // Search bar
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
            child: AppTextField(
              label: tr('search_transactions'),
              controller: _searchCtrl,
              prefixIcon: Icons.search,
              onChanged: (_) => _loadTransactions(),
            ),
          ).animate().fadeIn(duration: 300.ms),
          // Type filter chips
          SizedBox(
            height: 52,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              itemCount: types.length,
              separatorBuilder: (_, __) => const SizedBox(width: 8),
              itemBuilder: (_, i) {
                final t = types[i];
                final isSelected = _typeFilter == t['value'];
                final selectedValue = t['value']?.toString();
                return ChoiceChip(
                  label: Text(t['label'] as String),
                  selected: isSelected,
                  onSelected: (_) {
                    setState(() => _typeFilter = selectedValue);
                    _loadTransactions();
                  },
                  selectedColor: colors.primary.withValues(alpha: 0.2),
                  side: BorderSide(
                    color: isSelected ? colors.primary : colors.divider,
                  ),
                  labelStyle: TextStyle(
                    color: isSelected ? colors.primary : colors.textSecondary,
                    fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
                    fontSize: 13,
                  ),
                );
              },
            ),
          ).animate().fadeIn(delay: 100.ms),
          // List
          Expanded(
            child: RefreshIndicator(
              onRefresh: () => _loadTransactions(),
              color: colors.primary,
              backgroundColor: colors.bgCard,
              child: _buildListState(),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildListState() {
    final tr = context.tr;
    if (_loading) {
      return _shimmerList();
    }

    if (_items.isEmpty) {
      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(24),
        children: [
          SizedBox(
            height: MediaQuery.of(context).size.height * 0.5,
            child: _error != null
                ? Center(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.error_outline, color: Colors.redAccent, size: 40),
                        const SizedBox(height: 12),
                        Text(
                          _error!,
                          style: const TextStyle(color: Colors.redAccent, fontSize: 13),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 16),
                        ElevatedButton.icon(
                          onPressed: () => _loadTransactions(),
                          icon: const Icon(Icons.refresh, size: 16),
                          label: Text(tr('retry')),
                        ),
                      ],
                    ),
                  )
                : Center(
                    child: EmptyState(
                      icon: Icons.receipt_long_outlined,
                      title: tr('no_transactions_found'),
                    ),
                  ),
          ),
        ],
      );
    }

    return ListView.builder(
      controller: _scrollCtrl,
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.only(bottom: 20),
      itemCount: _items.length + (_loadingMore ? 1 : 0),
      itemBuilder: (ctx, i) {
        if (i == _items.length) {
          return Center(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: CircularProgressIndicator(color: context.colors.primary, strokeWidth: 2),
            ),
          );
        }
        final isFirst = i == 0 || _dayLabel(_items[i].createdAt) != _dayLabel(_items[i - 1].createdAt);
        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (isFirst)
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 4),
                child: Text(
                  _dayLabel(_items[i].createdAt),
                  style: TextStyle(color: context.colors.textSecondary, fontSize: 12, fontWeight: FontWeight.w600),
                ),
              ),
            Container(
              color: context.colors.bgCard,
              child: TransactionItem(
                transaction: _items[i],
              ),
            ),
            if (i < _items.length - 1 && _dayLabel(_items[i].createdAt) == _dayLabel(_items[i + 1].createdAt))
              Divider(height: 1, indent: 74, color: context.colors.divider),
          ],
        );
      },
    );
  }

  Widget _shimmerList() {
    return ListView.builder(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 0),
      itemCount: 8,
      itemBuilder: (_, __) => Padding(
        padding: const EdgeInsets.symmetric(vertical: 8),
        child: Row(children: [
          ShimmerBox(height: 44, width: 44, radius: 12),
          const SizedBox(width: 14),
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              ShimmerBox(height: 14, width: 200),
              const SizedBox(height: 6),
              ShimmerBox(height: 11, width: 120),
            ]),
          ),
          ShimmerBox(height: 16, width: 70),
        ]),
      ),
    );
  }

  String _dayLabel(String raw) {
    final tr = context.tr;
    try {
      final dt = DateTime.parse(raw);
      final now = DateTime.now();
      if (dt.year == now.year && dt.month == now.month && dt.day == now.day) {
        return tr('today');
      }
      if (dt.year == now.year && dt.month == now.month && dt.day == now.day - 1) {
        return tr('yesterday');
      }
      final months = ['Jan','Feb','Mar','Apr','May','Jun',
                      'Jul','Aug','Sep','Oct','Nov','Dec'];
      return '${months[dt.month - 1]} ${dt.day}, ${dt.year}';
    } catch (_) {
      return raw.length > 10 ? raw.substring(0, 10) : raw;
    }
  }
}

