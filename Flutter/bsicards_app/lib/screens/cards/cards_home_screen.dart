import 'package:flutter/material.dart';
import '../../config/app_colors.dart';
import '../../l10n/app_localizations.dart';
import '../../models/virtual_card.dart';
import '../../services/card_service.dart';
import '../../widgets/common_widgets.dart';
import 'digital_cards_screen.dart';

class CardsHomeScreen extends StatefulWidget {
  const CardsHomeScreen({super.key});

  @override
  State<CardsHomeScreen> createState() => _CardsHomeScreenState();
}

class _CardsHomeScreenState extends State<CardsHomeScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tab;
  List<VirtualCard> _digital = [];
  List<VirtualCard> _master  = [];
  List<VirtualCard> _visa    = [];
  List<Map<String, dynamic>> _masterPending = [];
  List<Map<String, dynamic>> _visaPending = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _tab = TabController(length: 3, vsync: this);
    _loadCards();
  }

  @override
  void dispose() {
    _tab.dispose();
    super.dispose();
  }

  Future<void> _loadCards() async {
    if (!mounted) return;
    setState(() => _loading = true);
    try {
      final results = await Future.wait([
        CardService.getDigitalCards(),
        CardService.getMasterCards(),
        CardService.getVisaCards(),
      ]);
      if (mounted) {
        setState(() {
          _digital = results[0] as List<VirtualCard>;
          final masterData = results[1] as Map<String, dynamic>;
          _master = masterData['cards'] as List<VirtualCard>;
          _masterPending = (masterData['pending'] as List?)
                  ?.whereType<Map<String, dynamic>>()
                  .toList() ??
              [];
          final visaData = results[2] as Map<String, dynamic>;
          _visa = visaData['cards'] as List<VirtualCard>;
          _visaPending = (visaData['pending'] as List?)
                  ?.whereType<Map<String, dynamic>>()
                  .toList() ??
              [];
          _loading = false;
        });
      }
    } catch (e, st) {
      debugPrint('⚠️ _loadCards error: $e\n$st');
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final tr = context.tr;
    final isInitialLoading = _loading &&
        _digital.isEmpty &&
        _master.isEmpty &&
        _visa.isEmpty &&
        _masterPending.isEmpty &&
        _visaPending.isEmpty;

    return Scaffold(
      backgroundColor: context.colors.bgDark,
      appBar: AppBar(
        title: Text(tr('my_cards')),
        bottom: TabBar(
          controller: _tab,
          indicatorColor: context.colors.primary,
          labelColor: context.colors.primary,
          unselectedLabelColor: context.colors.textSecondary,
          indicatorWeight: 3,
          tabs: [
            Tab(text: tr('digital')),
            Tab(text: tr('mastercard')),
            Tab(text: tr('visa')),
          ],
        ),
      ),
      body: isInitialLoading
          ? ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 100),
              children: [
                for (int i = 0; i < 3; i++) ...[
                  const ShimmerBox(height: 200, radius: 20),
                  if (i < 2) const SizedBox(height: 16),
                ],
              ],
            )
          : TabBarView(
              controller: _tab,
              children: [
                DigitalCardsScreen(cards: _digital, loading: _loading, onRefresh: _loadCards),
                MasterCardsScreen(
                  cards: _master,
                  pending: _masterPending,
                  loading: _loading,
                  onRefresh: _loadCards,
                ),
                VisaCardsScreen(
                  cards: _visa,
                  pending: _visaPending,
                  loading: _loading,
                  onRefresh: _loadCards,
                ),
              ],
            ),
    );
  }
}
