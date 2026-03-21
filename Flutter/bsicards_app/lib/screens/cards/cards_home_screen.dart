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
  List<VirtualCard> _digitalMasterCards = [];
  List<VirtualCard> _digitalVisaCards = [];
  bool _loadingDigitalMaster = true;
  bool _loadingDigitalVisa = true;
  int _tabIndex = 0;

  @override
  void initState() {
    super.initState();
    _loadDigitalMasterCards();
    _loadDigitalVisaCards();
  }

  Future<void> _loadDigitalMasterCards() async {
    if (!mounted) return;
    setState(() => _loadingDigitalMaster = true);
    try {
      final result = await CardService.getDigitalMasterCards();
      if (mounted) {
        setState(() {
          _digitalMasterCards = List<VirtualCard>.from(result['cards'] ?? []);
          _loadingDigitalMaster = false;
        });
      }
    } catch (e, st) {
      debugPrint('⚠️ _loadDigitalMasterCards error: $e\n$st');
      if (mounted) setState(() => _loadingDigitalMaster = false);
    }
  }

  Future<void> _loadDigitalVisaCards() async {
    if (!mounted) return;
    setState(() => _loadingDigitalVisa = true);
    try {
      final result = await CardService.getDigitalVisaCards();
      if (mounted) {
        setState(() {
          _digitalVisaCards = List<VirtualCard>.from(result['cards'] ?? []);
          _loadingDigitalVisa = false;
        });
      }
    } catch (e, st) {
      debugPrint('⚠️ _loadDigitalVisaCards error: $e\n$st');
      if (mounted) setState(() => _loadingDigitalVisa = false);
    }
  }

  void _showDigitalVisaApplySheet(BuildContext context, Function() onSuccess) {
    final tr = context.tr;
    final TextEditingController firstNameController = TextEditingController();
    final TextEditingController lastNameController = TextEditingController();

    showModalBottomSheet(
      context: context,
      builder: (BuildContext context) {
        return Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                tr('apply_digital_visa'),
                style: Theme.of(context).textTheme.titleLarge,
              ),
              const SizedBox(height: 16),
              TextField(
                controller: firstNameController,
                decoration: InputDecoration(
                  labelText: tr('first_name'),
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: lastNameController,
                decoration: InputDecoration(
                  labelText: tr('last_name'),
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () async {
                  final firstName = firstNameController.text.trim();
                  final lastName = lastNameController.text.trim();
                  if (firstName.isNotEmpty && lastName.isNotEmpty) {
                    Navigator.of(context).pop();
                    onSuccess();
                  } else {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text(tr('please_fill_all_fields'))),
                    );
                  }
                },
                child: Text(tr('submit')),
              ),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final tr = context.tr;
    return DefaultTabController(
      length: 2,
      initialIndex: _tabIndex,
      child: Scaffold(
        backgroundColor: context.colors.bgDark,
        appBar: AppBar(
          title: Text(tr('my_cards')),
          bottom: TabBar(
            tabs: [
              Tab(text: 'Digital Mastercard'),
              Tab(text: 'Digital Visacard'),
            ],
            onTap: (index) {
              setState(() => _tabIndex = index);
            },
          ),
        ),
        body: TabBarView(
          children: [
            DigitalCardsScreen(
              cards: _digitalMasterCards,
              loading: _loadingDigitalMaster,
              onRefresh: _loadDigitalMasterCards,
            ),
            DigitalCardsScreen(
              cards: _digitalVisaCards,
              loading: _loadingDigitalVisa,
              onRefresh: _loadDigitalVisaCards,
              fab: FloatingActionButton.extended(
                onPressed: () => _showDigitalVisaApplySheet(context, _loadDigitalVisaCards),
                backgroundColor: context.colors.primary,
                icon: const Icon(Icons.add),
                label: Text(tr('apply')),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
