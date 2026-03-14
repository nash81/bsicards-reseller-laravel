import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import 'dart:io';
import '../../config/app_colors.dart';
import '../../config/app_theme.dart';
import '../../l10n/app_localizations.dart';
import '../../models/virtual_card.dart';
import '../../providers/auth_provider.dart';
import '../../services/card_service.dart';
import '../../widgets/card_widget.dart';
import '../../widgets/common_widgets.dart';
import 'card_detail_screen.dart';

// ΓöÇΓöÇ Shared card list layout used by all three card screens ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ
class _CardListLayout extends StatelessWidget {
  final List<VirtualCard> cards;
  final bool loading;
  final Future<void> Function() onRefresh;
  final String emptyTitle;
  final String emptySubtitle;
  final Widget? fab;

  const _CardListLayout({
    required this.cards,
    required this.loading,
    required this.onRefresh,
    required this.emptyTitle,
    required this.emptySubtitle,
    this.fab,
  });

  @override
  Widget build(BuildContext context) {
    final tr = context.tr;
    return RefreshIndicator(
      onRefresh: onRefresh,
      color: context.colors.primary,
      backgroundColor: context.colors.bgCard,
      child: Stack(
        fit: StackFit.expand,
        children: [
          if (loading)
            CustomScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              slivers: [
                SliverPadding(
                  padding: const EdgeInsets.fromLTRB(20, 20, 20, 100),
                  sliver: SliverList(
                    delegate: SliverChildListDelegate([
                      for (int i = 0; i < 3; i++) ...[
                        const ShimmerBox(height: 200, radius: 20),
                        if (i < 2) const SizedBox(height: 16),
                      ],
                    ]),
                  ),
                ),
              ],
            )
          else
            CustomScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              slivers: [
                // ΓöÇΓöÇ "Issued Cards" heading ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ
                SliverPadding(
                  padding: EdgeInsets.fromLTRB(20, 20, 20, 12),
                  sliver: SliverToBoxAdapter(
                    child: Text(
                      tr('issued_cards'),
                      style: TextStyle(
                        color: context.colors.textPrimary,
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                ),
                // ΓöÇΓöÇ Cards list or empty state ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ
                SliverPadding(
                  padding: const EdgeInsets.fromLTRB(20, 0, 20, 0),
                  sliver: cards.isEmpty
                      ? SliverFillRemaining(
                          hasScrollBody: false,
                          child: EmptyState(
                            icon: Icons.credit_card_off_outlined,
                            title: emptyTitle,
                            subtitle: emptySubtitle,
                          ),
                        )
                      : SliverList(
                          delegate: SliverChildBuilderDelegate(
                            (ctx, i) => Padding(
                              padding: const EdgeInsets.only(bottom: 16),
                              child: CardWidget(
                                card: cards[i],
                                onTap: () async {
                                  await Navigator.push(
                                    ctx,
                                    MaterialPageRoute(
                                      builder: (_) =>
                                          CardDetailScreen(card: cards[i]),
                                    ),
                                  );
                                  await onRefresh();
                                },
                              ),
                            ),
                            childCount: cards.length,
                          ),
                        ),
                ),
                const SliverPadding(padding: EdgeInsets.only(bottom: 100)),
              ],
            ),
          if (fab != null) Positioned(bottom: 24, right: 24, child: fab!),
        ],
      ),
    );
  }
}

// ΓöÇΓöÇ Digital Cards ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ
class DigitalCardsScreen extends StatelessWidget {
  final List<VirtualCard> cards;
  final bool loading;
  final Future<void> Function() onRefresh;

  const DigitalCardsScreen({
    super.key,
    required this.cards,
    required this.loading,
    required this.onRefresh,
  });

  @override
  Widget build(BuildContext context) {
    final tr = context.tr;
    return _CardListLayout(
      cards: cards,
      loading: loading,
      onRefresh: onRefresh,
      emptyTitle: tr('no_digital_cards'),
      emptySubtitle: tr('digital_cards_empty_subtitle'),
      fab: FloatingActionButton.extended(
        onPressed: () => _showDigitalApplySheet(context, onRefresh),
        backgroundColor: context.colors.primary,
        icon: const Icon(Icons.add),
        label: Text(tr('apply')),
      ).animate().scale(delay: 500.ms, curve: Curves.elasticOut),
    );
  }
}

Future<void> _showDigitalApplySheet(
  BuildContext context,
  Future<void> Function() onRefresh,
) async {
  final tr = context.tr;
  final formKey = GlobalKey<FormState>();
  final firstNameCtrl = TextEditingController();
  final lastNameCtrl = TextEditingController();
  final dobCtrl = TextEditingController();
  final addressCtrl = TextEditingController();
  final cityCtrl = TextEditingController();
  final stateCtrl = TextEditingController();
  final postalCodeCtrl = TextEditingController();
  final phoneCtrl = TextEditingController();
  String? country;
  String? countryCode;
  var loading = false;
  double? digifee;

  try {
    final fees = await CardService.getCardFees();
    digifee = (fees['digifee'] as num?)?.toDouble();
  } catch (_) {}

  if (!context.mounted) return;

  await showModalBottomSheet(
    context: context,
    backgroundColor: context.colors.bgCard,
    isScrollControlled: true,
    shape: const RoundedRectangleBorder(
      borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
    ),
    builder: (sheetCtx) => StatefulBuilder(
      builder: (ctx, setModal) {
        Future<void> pickDob() async {
          DateTime initialDate;
          try {
            initialDate = DateTime.parse(dobCtrl.text.trim());
          } catch (_) {
            final now = DateTime.now();
            initialDate = DateTime(now.year - 18, now.month, now.day);
          }

          final now = DateTime.now();
          final picked = await showDatePicker(
            context: sheetCtx,
            initialDate: initialDate.isAfter(now) ? now : initialDate,
            firstDate: DateTime(1900, 1, 1),
            lastDate: now,
            helpText: tr('select_date_of_birth'),
          );

          if (picked == null) return;
          final mm = picked.month.toString().padLeft(2, '0');
          final dd = picked.day.toString().padLeft(2, '0');
          setModal(() => dobCtrl.text = '${picked.year}-$mm-$dd');
        }

        Future<void> pickCountry(FormFieldState<String> field) async {
          final selected = await _showSearchSelectDialog(
            sheetCtx,
            title: tr('select_country'),
            items: _countries
                .map((c) => _SearchItem(
                      value: c.code,
                      label: '${c.name} (${c.code})',
                      search: '${c.name} ${c.code}',
                    ))
                .toList(),
          );
          if (selected == null) return;
          setModal(() => country = selected);
          field.didChange(selected);
        }

        Future<void> pickCountryCode(FormFieldState<String> field) async {
          final selected = await _showSearchSelectDialog(
            sheetCtx,
            title: tr('select_country_code'),
            items: _countryPhoneCodes
                .map((c) => _SearchItem(
                      value: c.value,
                      label: '${c.label} (+${c.value})',
                      search: '${c.label} ${c.value}',
                    ))
                .toList(),
          );
          if (selected == null) return;
          setModal(() => countryCode = selected);
          field.didChange(selected);
        }

        Future<void> submit() async {
          if (!(formKey.currentState?.validate() ?? false)) return;
          if (country == null || countryCode == null) return;

          final userEmail = context.read<AuthProvider>().user?.email;
          if ((userEmail ?? '').isEmpty) {
            if (context.mounted && sheetCtx.mounted) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(tr('unable_detect_user_email')),
                  backgroundColor: AppTheme.error,
                ),
              );
            }
            return;
          }

          setModal(() => loading = true);
          try {
            final data = await CardService.applyDigitalCard(
              userEmail: userEmail!,
              firstName: firstNameCtrl.text.trim(),
              lastName: lastNameCtrl.text.trim(),
              dob: dobCtrl.text.trim(),
              address1: addressCtrl.text.trim(),
              city: cityCtrl.text.trim(),
              country: country!,
              state: stateCtrl.text.trim(),
              postalCode: postalCodeCtrl.text.trim(),
              countryCode: countryCode!,
              phone: phoneCtrl.text.trim(),
            );

            if (!context.mounted || !sheetCtx.mounted) return;
            Navigator.of(sheetCtx).pop();
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(data['message']?.toString() ?? tr('digital_card_application_submitted')),
                backgroundColor: AppTheme.success,
              ),
            );
            await onRefresh();
          } catch (e) {
            if (!context.mounted || !sheetCtx.mounted) return;
            setModal(() => loading = false);
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text(e.toString()), backgroundColor: AppTheme.error),
            );
          }
        }

        return Padding(
          padding: EdgeInsets.only(
            left: 24,
            right: 24,
            top: 24,
            bottom: MediaQuery.of(sheetCtx).viewInsets.bottom + 24,
          ),
          child: SingleChildScrollView(
            child: Form(
              key: formKey,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    tr('apply_for_digital_mastercard'),
                    style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700, color: context.colors.textPrimary),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    '${tr('new_card_fee_of')} \$${(digifee ?? 0).toStringAsFixed(2)} ${tr('will_be_charged')}',
                    style: TextStyle(color: context.colors.textSecondary, fontSize: 13),
                  ),
                  const SizedBox(height: 16),
                  AppTextField(
                    label: tr('first_name'),
                    controller: firstNameCtrl,
                    validator: (v) => (v ?? '').trim().isEmpty ? tr('first_name_required') : null,
                  ),
                  const SizedBox(height: 12),
                  AppTextField(
                    label: tr('last_name'),
                    controller: lastNameCtrl,
                    validator: (v) => (v ?? '').trim().isEmpty ? tr('last_name_required') : null,
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: dobCtrl,
                    readOnly: true,
                    onTap: loading ? null : pickDob,
                    validator: (v) => (v ?? '').trim().isEmpty ? tr('dob_required') : null,
                    style: TextStyle(color: context.colors.textPrimary, fontSize: 15),
                    decoration: InputDecoration(
                      labelText: tr('date_of_birth'),
                      hintText: tr('date_format_yyyy_mm_dd'),
                      prefixIcon: const Icon(Icons.calendar_today_outlined, size: 20),
                    ),
                  ),
                  const SizedBox(height: 12),
                  AppTextField(
                    label: tr('address'),
                    controller: addressCtrl,
                    validator: (v) => (v ?? '').trim().isEmpty ? tr('address_required') : null,
                  ),
                  const SizedBox(height: 12),
                  AppTextField(
                    label: tr('city'),
                    controller: cityCtrl,
                    validator: (v) => (v ?? '').trim().isEmpty ? tr('city_required') : null,
                  ),
                  const SizedBox(height: 12),
                  AppTextField(
                    label: tr('state'),
                    controller: stateCtrl,
                    validator: (v) => (v ?? '').trim().isEmpty ? tr('state_required') : null,
                  ),
                  const SizedBox(height: 12),
                  AppTextField(
                    label: tr('postal_code'),
                    controller: postalCodeCtrl,
                    validator: (v) => (v ?? '').trim().isEmpty ? tr('postal_code_required') : null,
                  ),
                  const SizedBox(height: 12),
                  FormField<String>(
                    initialValue: country,
                    validator: (_) => country == null || country!.isEmpty ? tr('country_required') : null,
                    builder: (field) => InkWell(
                      onTap: loading ? null : () => pickCountry(field),
                      child: InputDecorator(
                        decoration: InputDecoration(
                          labelText: tr('country'),
                          prefixIcon: const Icon(Icons.flag_outlined, size: 20),
                          errorText: field.errorText,
                        ),
                        child: Text(
                          _countryLabel(country) ?? tr('select_country'),
                          style: TextStyle(
                            color: country == null ? context.colors.textSecondary : context.colors.textPrimary,
                            fontSize: 15,
                          ),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                  FormField<String>(
                    initialValue: countryCode,
                    validator: (_) => countryCode == null || countryCode!.isEmpty ? tr('country_code_required') : null,
                    builder: (field) => InkWell(
                      onTap: loading ? null : () => pickCountryCode(field),
                      child: InputDecorator(
                        decoration: InputDecoration(
                          labelText: tr('country_code'),
                          prefixIcon: const Icon(Icons.call_outlined, size: 20),
                          errorText: field.errorText,
                        ),
                        child: Text(
                          _countryCodeLabel(countryCode) ?? tr('select_country_code'),
                          style: TextStyle(
                            color: countryCode == null ? context.colors.textSecondary : context.colors.textPrimary,
                            fontSize: 15,
                          ),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                  AppTextField(
                    label: tr('phone'),
                    controller: phoneCtrl,
                    keyboardType: TextInputType.phone,
                    validator: (v) => (v ?? '').trim().isEmpty ? tr('phone_required') : null,
                  ),
                  const SizedBox(height: 20),
                  AppButton(
                    label: tr('submit_application'),
                    isLoading: loading,
                    onTap: loading ? null : submit,
                  ),
                ],
              ),
            ),
          ),
        );
      },
    ),
  );
}

class _CountryOption {
  final String code;
  final String name;
  const _CountryOption(this.code, this.name);
}

class _PhoneCodeOption {
  final String value;
  final String label;
  const _PhoneCodeOption(this.value, this.label);
}

class _SearchItem {
  final String value;
  final String label;
  final String search;

  const _SearchItem({
    required this.value,
    required this.label,
    required this.search,
  });
}

String? _countryLabel(String? code) {
  if (code == null) return null;
  for (final c in _countries) {
    if (c.code == code) return '${c.name} (${c.code})';
  }
  return code;
}

String? _countryCodeLabel(String? value) {
  if (value == null) return null;
  for (final c in _countryPhoneCodes) {
    if (c.value == value) return '${c.label} (+${c.value})';
  }
  return value;
}

Future<String?> _showSearchSelectDialog(
  BuildContext context, {
  required String title,
  required List<_SearchItem> items,
}) async {
  final tr = context.tr;
  final searchCtrl = TextEditingController();
  List<_SearchItem> filtered = List<_SearchItem>.from(items);

  final picked = await showModalBottomSheet<String>(
    context: context,
    isScrollControlled: true,
    backgroundColor: context.colors.bgCard,
    shape: const RoundedRectangleBorder(
      borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
    ),
    builder: (sheetCtx) => StatefulBuilder(
      builder: (ctx, setModal) {
        final colors = context.colors;
        final maxSheetHeight = MediaQuery.of(sheetCtx).size.height * 0.78;
        return SafeArea(
          child: Padding(
            padding: EdgeInsets.only(
              left: 16,
              right: 16,
              top: 16,
              bottom: MediaQuery.of(sheetCtx).viewInsets.bottom + 16,
            ),
            child: SizedBox(
              height: maxSheetHeight,
              child: Column(
                children: [
                  Text(
                    title,
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                      color: colors.textPrimary,
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: searchCtrl,
                    style: TextStyle(color: colors.textPrimary),
                    decoration: InputDecoration(
                      hintText: tr('type_to_search'),
                      prefixIcon: const Icon(Icons.search),
                    ),
                    onChanged: (value) {
                      if (!ctx.mounted) return;
                      final q = value.trim().toLowerCase();
                      setModal(() {
                        filtered = q.isEmpty
                            ? List<_SearchItem>.from(items)
                            : items.where((e) => e.search.toLowerCase().contains(q)).toList();
                      });
                    },
                  ),
                  const SizedBox(height: 10),
                  Expanded(
                    child: filtered.isEmpty
                        ? Center(
                            child: Text(
                              tr('no_matching_results'),
                              style: TextStyle(color: colors.textSecondary),
                            ),
                          )
                        : ListView.separated(
                            itemCount: filtered.length,
                            separatorBuilder: (_, __) => Divider(height: 1, color: colors.divider),
                            itemBuilder: (_, i) => ListTile(
                              title: Text(
                                filtered[i].label,
                                style: TextStyle(color: colors.textPrimary),
                              ),
                              onTap: () => Navigator.pop(sheetCtx, filtered[i].value),
                            ),
                          ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    ),
  );

  // Do not dispose manually here; keyboard teardown can still reference controller briefly.
  return picked;
}

const List<_CountryOption> _countries = [
  _CountryOption('US', 'United States'),
  _CountryOption('GB', 'United Kingdom'),
  _CountryOption('CA', 'Canada'),
  _CountryOption('AU', 'Australia'),
  _CountryOption('DE', 'Germany'),
  _CountryOption('FR', 'France'),
  _CountryOption('ES', 'Spain'),
  _CountryOption('IT', 'Italy'),
  _CountryOption('NL', 'Netherlands'),
  _CountryOption('BE', 'Belgium'),
  _CountryOption('CH', 'Switzerland'),
  _CountryOption('AT', 'Austria'),
  _CountryOption('SE', 'Sweden'),
  _CountryOption('NO', 'Norway'),
  _CountryOption('DK', 'Denmark'),
  _CountryOption('FI', 'Finland'),
  _CountryOption('IE', 'Ireland'),
  _CountryOption('PT', 'Portugal'),
  _CountryOption('PL', 'Poland'),
  _CountryOption('CZ', 'Czech Republic'),
  _CountryOption('HU', 'Hungary'),
  _CountryOption('RO', 'Romania'),
  _CountryOption('GR', 'Greece'),
  _CountryOption('TR', 'Turkey'),
  _CountryOption('AE', 'United Arab Emirates'),
  _CountryOption('SA', 'Saudi Arabia'),
  _CountryOption('QA', 'Qatar'),
  _CountryOption('KW', 'Kuwait'),
  _CountryOption('EG', 'Egypt'),
  _CountryOption('ZA', 'South Africa'),
  _CountryOption('NG', 'Nigeria'),
  _CountryOption('KE', 'Kenya'),
  _CountryOption('IN', 'India'),
  _CountryOption('PK', 'Pakistan'),
  _CountryOption('BD', 'Bangladesh'),
  _CountryOption('LK', 'Sri Lanka'),
  _CountryOption('NP', 'Nepal'),
  _CountryOption('CN', 'China'),
  _CountryOption('JP', 'Japan'),
  _CountryOption('KR', 'South Korea'),
  _CountryOption('SG', 'Singapore'),
  _CountryOption('MY', 'Malaysia'),
  _CountryOption('TH', 'Thailand'),
  _CountryOption('ID', 'Indonesia'),
  _CountryOption('PH', 'Philippines'),
  _CountryOption('VN', 'Vietnam'),
  _CountryOption('HK', 'Hong Kong'),
  _CountryOption('NZ', 'New Zealand'),
  _CountryOption('BR', 'Brazil'),
  _CountryOption('AR', 'Argentina'),
  _CountryOption('MX', 'Mexico'),
  _CountryOption('CL', 'Chile'),
  _CountryOption('CO', 'Colombia'),
  _CountryOption('PE', 'Peru'),
];

const List<_PhoneCodeOption> _countryPhoneCodes = [
  _PhoneCodeOption('1', 'US/CA'),
  _PhoneCodeOption('7', 'Russia/Kazakhstan'),
  _PhoneCodeOption('20', 'Egypt'),
  _PhoneCodeOption('27', 'South Africa'),
  _PhoneCodeOption('30', 'Greece'),
  _PhoneCodeOption('31', 'Netherlands'),
  _PhoneCodeOption('32', 'Belgium'),
  _PhoneCodeOption('33', 'France'),
  _PhoneCodeOption('34', 'Spain'),
  _PhoneCodeOption('36', 'Hungary'),
  _PhoneCodeOption('39', 'Italy'),
  _PhoneCodeOption('40', 'Romania'),
  _PhoneCodeOption('41', 'Switzerland'),
  _PhoneCodeOption('43', 'Austria'),
  _PhoneCodeOption('44', 'United Kingdom'),
  _PhoneCodeOption('45', 'Denmark'),
  _PhoneCodeOption('46', 'Sweden'),
  _PhoneCodeOption('47', 'Norway'),
  _PhoneCodeOption('48', 'Poland'),
  _PhoneCodeOption('49', 'Germany'),
  _PhoneCodeOption('52', 'Mexico'),
  _PhoneCodeOption('54', 'Argentina'),
  _PhoneCodeOption('55', 'Brazil'),
  _PhoneCodeOption('56', 'Chile'),
  _PhoneCodeOption('57', 'Colombia'),
  _PhoneCodeOption('60', 'Malaysia'),
  _PhoneCodeOption('61', 'Australia'),
  _PhoneCodeOption('62', 'Indonesia'),
  _PhoneCodeOption('63', 'Philippines'),
  _PhoneCodeOption('64', 'New Zealand'),
  _PhoneCodeOption('65', 'Singapore'),
  _PhoneCodeOption('66', 'Thailand'),
  _PhoneCodeOption('81', 'Japan'),
  _PhoneCodeOption('82', 'South Korea'),
  _PhoneCodeOption('84', 'Vietnam'),
  _PhoneCodeOption('86', 'China'),
  _PhoneCodeOption('90', 'Turkey'),
  _PhoneCodeOption('91', 'India'),
  _PhoneCodeOption('92', 'Pakistan'),
  _PhoneCodeOption('93', 'Afghanistan'),
  _PhoneCodeOption('94', 'Sri Lanka'),
  _PhoneCodeOption('95', 'Myanmar'),
  _PhoneCodeOption('98', 'Iran'),
  _PhoneCodeOption('211', 'South Sudan'),
  _PhoneCodeOption('212', 'Morocco'),
  _PhoneCodeOption('213', 'Algeria'),
  _PhoneCodeOption('216', 'Tunisia'),
  _PhoneCodeOption('218', 'Libya'),
  _PhoneCodeOption('220', 'Gambia'),
  _PhoneCodeOption('221', 'Senegal'),
  _PhoneCodeOption('223', 'Mali'),
  _PhoneCodeOption('224', 'Guinea'),
  _PhoneCodeOption('225', 'Ivory Coast'),
  _PhoneCodeOption('226', 'Burkina Faso'),
  _PhoneCodeOption('227', 'Niger'),
  _PhoneCodeOption('228', 'Togo'),
  _PhoneCodeOption('229', 'Benin'),
  _PhoneCodeOption('230', 'Mauritius'),
  _PhoneCodeOption('231', 'Liberia'),
  _PhoneCodeOption('232', 'Sierra Leone'),
  _PhoneCodeOption('233', 'Ghana'),
  _PhoneCodeOption('234', 'Nigeria'),
  _PhoneCodeOption('254', 'Kenya'),
  _PhoneCodeOption('255', 'Tanzania'),
  _PhoneCodeOption('256', 'Uganda'),
  _PhoneCodeOption('260', 'Zambia'),
  _PhoneCodeOption('263', 'Zimbabwe'),
  _PhoneCodeOption('351', 'Portugal'),
  _PhoneCodeOption('352', 'Luxembourg'),
  _PhoneCodeOption('353', 'Ireland'),
  _PhoneCodeOption('355', 'Albania'),
  _PhoneCodeOption('356', 'Malta'),
  _PhoneCodeOption('357', 'Cyprus'),
  _PhoneCodeOption('358', 'Finland'),
  _PhoneCodeOption('359', 'Bulgaria'),
  _PhoneCodeOption('370', 'Lithuania'),
  _PhoneCodeOption('371', 'Latvia'),
  _PhoneCodeOption('372', 'Estonia'),
  _PhoneCodeOption('380', 'Ukraine'),
  _PhoneCodeOption('420', 'Czech Republic'),
  _PhoneCodeOption('421', 'Slovakia'),
  _PhoneCodeOption('971', 'United Arab Emirates'),
  _PhoneCodeOption('972', 'Israel'),
  _PhoneCodeOption('973', 'Bahrain'),
  _PhoneCodeOption('974', 'Qatar'),
  _PhoneCodeOption('975', 'Bhutan'),
  _PhoneCodeOption('976', 'Mongolia'),
  _PhoneCodeOption('977', 'Nepal'),
  _PhoneCodeOption('966', 'Saudi Arabia'),
  _PhoneCodeOption('965', 'Kuwait'),
];

// ΓöÇΓöÇ MasterCards ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ
class MasterCardsScreen extends StatelessWidget {
  final List<VirtualCard> cards;
  final List<Map<String, dynamic>> pending;
  final bool loading;
  final Future<void> Function() onRefresh;

  const MasterCardsScreen({
    super.key,
    required this.cards,
    required this.pending,
    required this.loading,
    required this.onRefresh,
  });

  @override
  Widget build(BuildContext context) {
    final tr = context.tr;
    return _CardsWithPendingTabs(
      cards: cards,
      pending: pending,
      loading: loading,
      onRefresh: onRefresh,
      emptyTitle: tr('no_mastercards'),
      emptySubtitle: tr('mastercards_empty_subtitle'),
      fab: FloatingActionButton.extended(
        onPressed: () => _showMasterApplySheet(context, onRefresh),
        backgroundColor: context.colors.primary,
        icon: const Icon(Icons.add),
        label: Text(tr('apply')),
      ).animate().scale(delay: 500.ms, curve: Curves.elasticOut),
    );
  }
}

// ΓöÇΓöÇ Visa Cards ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ
class VisaCardsScreen extends StatelessWidget {
  final List<VirtualCard> cards;
  final List<Map<String, dynamic>> pending;
  final bool loading;
  final Future<void> Function() onRefresh;

  const VisaCardsScreen({
    super.key,
    required this.cards,
    required this.pending,
    required this.loading,
    required this.onRefresh,
  });

  @override
  Widget build(BuildContext context) {
    final tr = context.tr;
    return _CardsWithPendingTabs(
      cards: cards,
      pending: pending,
      loading: loading,
      onRefresh: onRefresh,
      emptyTitle: tr('no_visa_cards'),
      emptySubtitle: tr('visa_cards_empty_subtitle'),
      fab: FloatingActionButton.extended(
        onPressed: () => _showVisaApplySheet(context, onRefresh),
        backgroundColor: context.colors.primary,
        icon: const Icon(Icons.add),
        label: Text(tr('apply')),
      ).animate().scale(delay: 500.ms, curve: Curves.elasticOut),
    );
  }
}

class _CardsWithPendingTabs extends StatelessWidget {
  final List<VirtualCard> cards;
  final List<Map<String, dynamic>> pending;
  final bool loading;
  final Future<void> Function() onRefresh;
  final String emptyTitle;
  final String emptySubtitle;
  final Widget? fab;

  const _CardsWithPendingTabs({
    required this.cards,
    required this.pending,
    required this.loading,
    required this.onRefresh,
    required this.emptyTitle,
    required this.emptySubtitle,
    this.fab,
  });

  String _pendingStatusLabel(BuildContext context, String status) {
    final normalized = status.toLowerCase();
    if (normalized == 'requested' || normalized == 'pending') {
      return context.tr('pending');
    }
    if (normalized == 'rejected' || normalized == 'declined') {
      return context.tr('rejected');
    }
    if (normalized == 'approved' || normalized == 'success' || normalized == 'accepted') {
      return context.tr('approved');
    }
    return status.isEmpty ? context.tr('pending') : status;
  }

  void _openPendingSheet(BuildContext context) {
    final tr = context.tr;
    showModalBottomSheet(
      context: context,
      backgroundColor: context.colors.bgCard,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (_) => DraggableScrollableSheet(
        expand: false,
        initialChildSize: pending.length <= 3 ? 0.45 : 0.7,
        minChildSize: 0.3,
        maxChildSize: 0.85,
        builder: (_, controller) => Column(
          children: [
            const SizedBox(height: 10),
            Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: context.colors.divider,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            const SizedBox(height: 14),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: Row(
                children: [
                  Text(
                    tr('pending_applications'),
                    style: TextStyle(
                      color: context.colors.textPrimary,
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: AppTheme.warning.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      '${pending.length}',
                      style: const TextStyle(
                        color: AppTheme.warning,
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            Divider(height: 1, color: context.colors.divider),
            Expanded(
              child: pending.isEmpty
                  ? Center(
                      child: EmptyState(
                        icon: Icons.pending_actions_outlined,
                        title: tr('no_pending_cards'),
                        subtitle: tr('requested_cards_will_appear'),
                      ),
                    )
                  : ListView.separated(
                      controller: controller,
                      padding: const EdgeInsets.fromLTRB(20, 12, 20, 32),
                      itemCount: pending.length,
                      separatorBuilder: (_, __) => const SizedBox(height: 10),
                      itemBuilder: (_, i) {
                        final item = pending[i];
                        final name = (item['nameoncard'] ?? tr('card_request')).toString();
                        final email = (item['useremail'] ?? '').toString();
                        final status = (item['status'] ?? 'requested').toString();
                        final statusColor = status.toLowerCase() == 'rejected'
                            ? AppTheme.error
                            : AppTheme.warning;
                        return Container(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                          decoration: BoxDecoration(
                            color: context.colors.surfaceLight,
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: statusColor.withValues(alpha: 0.3)),
                          ),
                          child: Row(
                            children: [
                              Container(
                                width: 40,
                                height: 40,
                                decoration: BoxDecoration(
                                  color: statusColor.withValues(alpha: 0.12),
                                  shape: BoxShape.circle,
                                ),
                                child: Icon(
                                  status.toLowerCase() == 'rejected'
                                      ? Icons.cancel_outlined
                                      : Icons.pending_outlined,
                                  color: statusColor,
                                  size: 20,
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      name,
                                      style: TextStyle(
                                        color: context.colors.textPrimary,
                                        fontWeight: FontWeight.w600,
                                        fontSize: 13,
                                      ),
                                    ),
                                    if (email.isNotEmpty)
                                      Padding(
                                        padding: const EdgeInsets.only(top: 2),
                                        child: Text(
                                          email,
                                          style: TextStyle(
                                              color: context.colors.textSecondary, fontSize: 11.5),
                                        ),
                                      ),
                                  ],
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                  color: statusColor.withValues(alpha: 0.12),
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Text(
                                  _pendingStatusLabel(context, status),
                                  style: TextStyle(
                                    color: statusColor,
                                    fontSize: 11,
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        );
                      },
                    ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final tr = context.tr;
    return RefreshIndicator(
      onRefresh: onRefresh,
      color: context.colors.primary,
      backgroundColor: context.colors.bgCard,
      child: Stack(
        fit: StackFit.expand,
        children: [
          if (loading)
            ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 100),
              children: [
                for (int i = 0; i < 3; i++) ...[
                  const ShimmerBox(height: 200, radius: 20),
                  if (i < 2) const SizedBox(height: 16),
                ],
              ],
            )
          else
            CustomScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              slivers: [
                // ΓöÇΓöÇ Header: Issued Cards + Pending button ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ
                SliverPadding(
                  padding: const EdgeInsets.fromLTRB(20, 20, 20, 12),
                  sliver: SliverToBoxAdapter(
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      crossAxisAlignment: CrossAxisAlignment.center,
                      children: [
                        Text(
                          tr('issued_cards'),
                          style: TextStyle(
                            color: AppTheme.textPrimary,
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        GestureDetector(
                          onTap: () => _openPendingSheet(context),
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 14, vertical: 7),
                            decoration: BoxDecoration(
                              color: AppTheme.warning.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(
                                  color: AppTheme.warning.withValues(alpha: 0.35)),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(Icons.pending_outlined,
                                    color: AppTheme.warning, size: 15),
                                const SizedBox(width: 5),
                                Text(
                                  tr('pending'),
                                  style: const TextStyle(
                                    color: AppTheme.warning,
                                    fontSize: 12,
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                                if (pending.isNotEmpty) ...[
                                  const SizedBox(width: 6),
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                        horizontal: 6, vertical: 1),
                                    decoration: BoxDecoration(
                                      color: AppTheme.warning,
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                    child: Text(
                                      '${pending.length}',
                                      style: const TextStyle(
                                        color: Colors.white,
                                        fontSize: 10,
                                        fontWeight: FontWeight.w800,
                                      ),
                                    ),
                                  ),
                                ],
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                // ΓöÇΓöÇ Cards list ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ
                SliverPadding(
                  padding: const EdgeInsets.fromLTRB(20, 0, 20, 0),
                  sliver: cards.isEmpty
                      ? SliverFillRemaining(
                          hasScrollBody: false,
                          child: EmptyState(
                            icon: Icons.credit_card_off_outlined,
                            title: emptyTitle,
                            subtitle: emptySubtitle,
                          ),
                        )
                      : SliverList(
                          delegate: SliverChildBuilderDelegate(
                            (ctx, i) => Padding(
                              padding: const EdgeInsets.only(bottom: 16),
                              child: CardWidget(
                                card: cards[i],
                                onTap: () async {
                                  await Navigator.push(
                                    ctx,
                                    MaterialPageRoute(
                                      builder: (_) =>
                                          CardDetailScreen(card: cards[i]),
                                    ),
                                  );
                                  await onRefresh();
                                },
                              ),
                            ),
                            childCount: cards.length,
                          ),
                        ),
                ),
                const SliverPadding(padding: EdgeInsets.only(bottom: 100)),
              ],
            ),
          if (fab != null) Positioned(bottom: 24, right: 24, child: fab!),
        ],
      ),
    );
  }
}


Future<void> _showMasterApplySheet(
  BuildContext context,
  Future<void> Function() onRefresh,
) async {
  final tr = context.tr;
  final formKey = GlobalKey<FormState>();
  final pinCtrl = TextEditingController();
  var loading = false;
  double? issueFee;

  try {
    final fees = await CardService.getCardFees();
    issueFee = (fees['bsiissue_fee'] as num?)?.toDouble();
  } catch (_) {}

  if (!context.mounted) return;

  await showModalBottomSheet(
    context: context,
    backgroundColor: context.colors.bgCard,
    isScrollControlled: true,
    shape: const RoundedRectangleBorder(
      borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
    ),
    builder: (sheetCtx) => StatefulBuilder(
      builder: (ctx, setModal) {
        Future<void> submit() async {
          if (!(formKey.currentState?.validate() ?? false)) return;
          setModal(() => loading = true);
          try {
            final data = await CardService.applyMasterCard(pin: pinCtrl.text.trim());
            if (!context.mounted) return;
            if (Navigator.of(sheetCtx).canPop()) Navigator.of(sheetCtx).pop();
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(data['message']?.toString() ?? tr('mastercard_request_submitted')),
                backgroundColor: AppTheme.success,
              ),
            );
            await onRefresh();
          } catch (e) {
            if (!context.mounted) return;
            setModal(() => loading = false);
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text(e.toString()), backgroundColor: AppTheme.error),
            );
          }
        }

        return Padding(
          padding: EdgeInsets.only(
            left: 24,
            right: 24,
            top: 24,
            bottom: MediaQuery.of(sheetCtx).viewInsets.bottom + 24,
          ),
          child: Form(
            key: formKey,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  tr('apply_for_mastercard'),
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700, color: context.colors.textPrimary),
                ),
                const SizedBox(height: 8),
                Text(
                  '${tr('new_card_fee_of')} \$${(issueFee ?? 0).toStringAsFixed(2)} ${tr('plus_minimum_load_10')}',
                  style: TextStyle(color: context.colors.textSecondary, fontSize: 13),
                ),
                const SizedBox(height: 16),
                AppTextField(
                  label: tr('pin'),
                  hint: tr('pin_4_6_digits'),
                  controller: pinCtrl,
                  keyboardType: TextInputType.number,
                  validator: (v) {
                    final t = (v ?? '').trim();
                    if (t.isEmpty) return tr('pin_required');
                    if (!RegExp(r'^\d{4,6}$').hasMatch(t)) return tr('enter_valid_pin');
                    return null;
                  },
                ),
                const SizedBox(height: 20),
                AppButton(
                  label: tr('submit_application'),
                  isLoading: loading,
                  onTap: loading ? null : submit,
                ),
              ],
            ),
          ),
        );
      },
    ),
  );

}

Future<void> _showVisaApplySheet(
  BuildContext context,
  Future<void> Function() onRefresh,
) async {
  final tr = context.tr;
  final formKey = GlobalKey<FormState>();
  final pinCtrl = TextEditingController();
  final dobCtrl = TextEditingController();
  final nationalIdCtrl = TextEditingController();
  File? userPhotoFile;
  File? nationalIdImageFile;
  var loading = false;
  double? issueFee;

  try {
    final fees = await CardService.getCardFees();
    issueFee = (fees['bsiissue_fee'] as num?)?.toDouble();
  } catch (_) {}

  if (!context.mounted) return;

  await showModalBottomSheet(
    context: context,
    backgroundColor: context.colors.bgCard,
    isScrollControlled: true,
    shape: const RoundedRectangleBorder(
      borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
    ),
    builder: (sheetCtx) => StatefulBuilder(
      builder: (ctx, setModal) {
        Future<void> pickDob() async {
          DateTime initialDate;
          try {
            initialDate = DateTime.parse(dobCtrl.text.trim());
          } catch (_) {
            final now = DateTime.now();
            initialDate = DateTime(now.year - 18, now.month, now.day);
          }

          final now = DateTime.now();
          final picked = await showDatePicker(
            context: sheetCtx,
            initialDate: initialDate.isAfter(now) ? now : initialDate,
            firstDate: DateTime(1900, 1, 1),
            lastDate: now,
            helpText: tr('select_date_of_birth'),
          );

          if (picked == null) return;
          final mm = picked.month.toString().padLeft(2, '0');
          final dd = picked.day.toString().padLeft(2, '0');
          setModal(() => dobCtrl.text = '${picked.year}-$mm-$dd');
        }

        Future<void> pickImage({required bool isUserPhoto}) async {
          final picked = await ImagePicker().pickImage(
            source: ImageSource.gallery,
            imageQuality: 75,
            maxWidth: 1600,
          );
          if (picked == null) return;
          setModal(() {
            if (isUserPhoto) {
              userPhotoFile = File(picked.path);
            } else {
              nationalIdImageFile = File(picked.path);
            }
          });
        }

        Future<void> submit() async {
          if (!(formKey.currentState?.validate() ?? false)) return;
          setModal(() => loading = true);
          try {
            final data = await CardService.applyVisaCard(
              pin: pinCtrl.text.trim(),
              dob: dobCtrl.text.trim(),
              nationalIdNumber: nationalIdCtrl.text.trim(),
              userPhotoFile: userPhotoFile,
              nationalIdImageFile: nationalIdImageFile,
            );
            if (!context.mounted) return;
            if (Navigator.of(sheetCtx).canPop()) Navigator.of(sheetCtx).pop();
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(data['message']?.toString() ?? tr('visa_application_submitted')),
                backgroundColor: AppTheme.success,
              ),
            );
            await onRefresh();
          } catch (e) {
            if (!context.mounted) return;
            setModal(() => loading = false);
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text(e.toString()), backgroundColor: AppTheme.error),
            );
          }
        }

        return Padding(
          padding: EdgeInsets.only(
            left: 24,
            right: 24,
            top: 24,
            bottom: MediaQuery.of(sheetCtx).viewInsets.bottom + 24,
          ),
          child: SingleChildScrollView(
            child: Form(
              key: formKey,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    tr('apply_for_visa_card'),
                    style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700, color: context.colors.textPrimary),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    '${tr('new_card_fee_of')} \$${(issueFee ?? 0).toStringAsFixed(2)} ${tr('plus_minimum_load_10')}',
                    style: TextStyle(color: context.colors.textSecondary, fontSize: 13),
                  ),
                  const SizedBox(height: 16),
                  AppTextField(
                    label: tr('pin'),
                    hint: tr('pin_4_6_digits'),
                    controller: pinCtrl,
                    keyboardType: TextInputType.number,
                    validator: (v) {
                      final t = (v ?? '').trim();
                      if (t.isEmpty) return tr('pin_required');
                      if (!RegExp(r'^\d{4,6}$').hasMatch(t)) return tr('enter_valid_pin');
                      return null;
                    },
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: dobCtrl,
                    readOnly: true,
                    onTap: loading ? null : pickDob,
                    validator: (v) => (v ?? '').trim().isEmpty ? tr('dob_required') : null,
                    style: TextStyle(color: context.colors.textPrimary, fontSize: 15),
                    decoration: InputDecoration(
                      labelText: tr('date_of_birth'),
                      hintText: tr('date_format_yyyy_mm_dd'),
                      prefixIcon: const Icon(Icons.calendar_today_outlined, size: 20),
                    ),
                  ),
                  const SizedBox(height: 12),
                  AppTextField(
                    label: tr('national_id_number'),
                    controller: nationalIdCtrl,
                    validator: (v) => (v ?? '').trim().isEmpty ? tr('national_id_number_required') : null,
                  ),
                  const SizedBox(height: 12),
                  AppButton(
                    label: userPhotoFile == null ? tr('pick_user_photo') : tr('change_user_photo'),
                    outlined: true,
                    icon: Icons.image_outlined,
                    onTap: loading ? null : () => pickImage(isUserPhoto: true),
                  ),
                  if (userPhotoFile != null) ...[
                    const SizedBox(height: 6),
                    Text(
                      userPhotoFile!.path.split('\\').last,
                      style: TextStyle(color: context.colors.textSecondary, fontSize: 12),
                    ),
                  ],
                  const SizedBox(height: 12),
                  AppButton(
                    label: nationalIdImageFile == null ? tr('pick_national_id_image') : tr('change_national_id_image'),
                    outlined: true,
                    icon: Icons.badge_outlined,
                    onTap: loading ? null : () => pickImage(isUserPhoto: false),
                  ),
                  if (nationalIdImageFile != null) ...[
                    const SizedBox(height: 6),
                    Text(
                      nationalIdImageFile!.path.split('\\').last,
                      style: TextStyle(color: context.colors.textSecondary, fontSize: 12),
                    ),
                  ],
                  if (userPhotoFile == null || nationalIdImageFile == null) ...[
                    const SizedBox(height: 10),
                    Text(
                      tr('select_both_images_before_submitting'),
                      style: TextStyle(color: Colors.orangeAccent, fontSize: 12),
                    ),
                  ],
                  const SizedBox(height: 20),
                  AppButton(
                    label: tr('submit_application'),
                    isLoading: loading,
                    onTap: (loading || userPhotoFile == null || nationalIdImageFile == null)
                        ? null
                        : submit,
                  ),
                ],
              ),
            ),
          ),
        );
      },
    ),
  );

}

