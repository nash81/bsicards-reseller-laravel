import 'package:flutter/material.dart';
import '../../config/app_colors.dart';
import '../../l10n/app_localizations.dart';
import '../../models/virtual_card.dart';
import '../../services/card_service.dart';
import 'digital_cards_screen.dart';

class CardsHomeScreen extends StatefulWidget {
  const CardsHomeScreen({super.key});

  @override
  State<CardsHomeScreen> createState() => _CardsHomeScreenState();
}

class _CardsHomeScreenState extends State<CardsHomeScreen> {
  List<VirtualCard> _digital = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadCards();
  }

  Future<void> _loadCards() async {
    if (!mounted) return;
    setState(() => _loading = true);
    try {
      final cards = await CardService.getDigitalCards();
      if (mounted) {
        setState(() {
          _digital = cards;
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
    return Scaffold(
      backgroundColor: context.colors.bgDark,
      appBar: AppBar(
        title: Text(tr('my_cards')),
      ),
      body: DigitalCardsScreen(
        cards: _digital,
        loading: _loading,
        onRefresh: _loadCards,
      ),
    );
  }
}
