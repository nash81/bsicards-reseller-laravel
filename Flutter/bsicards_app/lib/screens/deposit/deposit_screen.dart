import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:image_picker/image_picker.dart';
import 'package:webview_flutter/webview_flutter.dart';
import '../../config/app_colors.dart';
import '../../config/app_theme.dart';
import '../../l10n/app_localizations.dart';
import '../../models/gateway.dart';
import '../../services/deposit_service.dart';
import '../../widgets/common_widgets.dart';

class DepositScreen extends StatefulWidget {
  const DepositScreen({super.key});

  @override
  State<DepositScreen> createState() => _DepositScreenState();
}

class _DepositScreenState extends State<DepositScreen> {
  List<Gateway> _gateways = [];
  bool _loading = true;
  Gateway? _selected;
  final _amountCtrl = TextEditingController();
  bool _processing = false;

  @override
  void initState() {
    super.initState();
    _loadGateways();
  }

  @override
  void dispose() {
    _amountCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadGateways() async {
    setState(() => _loading = true);
    try {
      final gateways = await DepositService.getGateways();
      if (mounted) setState(() { _gateways = gateways; _loading = false; });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _initiateDeposit() async {
    if (_selected == null) return;
    final amount = double.tryParse(_amountCtrl.text);
    if (amount == null || amount <= 0) {
      _showSnack(context.tr('enter_valid_amount'), isError: true);
      return;
    }
    setState(() => _processing = true);
    try {
      final result = await DepositService.initiateDeposit(
        gatewayCode: _selected!.gatewayCode,
        amount: amount,
      );

      debugPrint('📤 Deposit Response: $result');
      debugPrint('   - type: ${result['type']}');
      debugPrint('   - redirect_url: ${result['redirect_url']}');
      debugPrint('   - tnx: ${result['tnx']}');

      if (!mounted) return;
      setState(() => _processing = false);

      if (result['type'] == 'auto' && result['redirect_url'] != null) {
        final tnx = result['tnx'] as String;
        final url = result['redirect_url'] as String;
        debugPrint('✅ Opening WebView with URL: $url');
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => PaymentWebView(
              url: url,
              tnx: tnx,
            ),
          ),
        );
      } else if (result['type'] == 'manual') {
        Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => ManualDepositProofScreen(payload: result)),
        );
      } else {
        _showSnack(context.tr('deposit_pending_confirmation'), isError: false);
      }
    } catch (e) {
      debugPrint('❌ Deposit error: $e');
      if (mounted) {
        setState(() => _processing = false);
        _showSnack(e.toString(), isError: true);
      }
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

  @override
  Widget build(BuildContext context) {
    final tr = context.tr;
    final colors = context.colors;
    return Scaffold(
      backgroundColor: colors.bgDark,
      appBar: AppBar(title: Text(tr('add_money'))),
      body: _loading
          ? Center(child: CircularProgressIndicator(color: colors.primary))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(tr('select_payment_gateway'),
                      style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w700,
                          color: colors.textPrimary))
                      .animate().fadeIn(),
                  const SizedBox(height: 16),
                  _gatewayGrid(),
                  if (_selected != null) ...[
                    const SizedBox(height: 28),
                    _amountSection(),
                  ],
                ],
              ),
            ),
    );
  }

  Widget _gatewayGrid() {
    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        crossAxisSpacing: 12,
        mainAxisSpacing: 12,
        mainAxisExtent: 112,
      ),
      itemCount: _gateways.length,
      itemBuilder: (_, i) {
        final colors = context.colors;
        final gw = _gateways[i];
        final isSelected = _selected?.id == gw.id;
        return GestureDetector(
          onTap: () => setState(() => _selected = gw),
          child: Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: isSelected
                  ? colors.primary.withValues(alpha: 0.15)
                  : colors.bgCard,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(
                color: isSelected ? colors.primary : colors.divider,
                width: isSelected ? 1.5 : 1,
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _gatewayLogo(gw),
                    const Spacer(),
                    if (isSelected)
                      Icon(Icons.check_circle,
                          color: colors.primary, size: 18),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  gw.name,
                  style: TextStyle(
                      color: colors.textPrimary,
                      fontWeight: FontWeight.w600,
                      fontSize: 13),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                Text(
                  gw.currency,
                  style: TextStyle(
                      color: colors.textSecondary, fontSize: 11),
                ),
              ],
            ),
          ),
        )
            .animate(delay: Duration(milliseconds: 60 * i))
            .fadeIn()
            .scale(begin: const Offset(0.9, 0.9));
      },
    );
  }

  Widget _gatewayLogo(Gateway gw) {
    final colors = context.colors;
    return Container(
      width: 36,
      height: 36,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(8),
      ),
      clipBehavior: Clip.antiAlias,
      child: gw.logo.isNotEmpty
          ? Image.network(
              gw.logo,
              fit: BoxFit.contain,
              loadingBuilder: (_, child, progress) => progress == null
                  ? child
                  : Center(
                      child: SizedBox(
                        width: 16,
                        height: 16,
                        child: CircularProgressIndicator(
                            strokeWidth: 1.5, color: colors.primary),
                      ),
                    ),
              errorBuilder: (_, __, ___) => Icon(
                Icons.payment,
                color: colors.primary,
                size: 20,
              ),
            )
          : Icon(Icons.payment, color: colors.primary, size: 20),
    );
  }

  Widget _amountSection() {
    final colors = context.colors;
    final gw = _selected!;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('${context.tr('amount_in')} ${gw.currency}',
            style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w700,
                color: colors.textPrimary))
            .animate().fadeIn(),
        const SizedBox(height: 12),
        AppTextField(
          label: context.tr('enter_amount'),
          hint: '0.00',
          controller: _amountCtrl,
          prefixIcon: Icons.attach_money,
          keyboardType: const TextInputType.numberWithOptions(decimal: true),
        ).animate().fadeIn(delay: 100.ms),
        const SizedBox(height: 12),
        Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: colors.surface,
            borderRadius: BorderRadius.circular(12),
          ),
          child: Column(
            children: [
              _feeRow(context.tr('min_max'),
                  '\$${gw.minimumDeposit.toStringAsFixed(0)} / \$${gw.maximumDeposit.toStringAsFixed(0)}'),
              const SizedBox(height: 6),
              _feeRow(context.tr('fee'),
                  gw.chargeType == 'percentage'
                      ? '${gw.charge}%'
                      : '\$${gw.charge.toStringAsFixed(2)}'),
            ],
          ),
        ).animate().fadeIn(delay: 150.ms),
        const SizedBox(height: 20),
        AppButton(
          label: context.tr('proceed_to_payment'),
          isLoading: _processing,
          onTap: _initiateDeposit,
          icon: Icons.arrow_forward_rounded,
        ).animate().fadeIn(delay: 200.ms),
      ],
    );
  }

  Widget _feeRow(String label, String value) {
    final colors = context.colors;
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label,
            style: TextStyle(
                color: colors.textSecondary, fontSize: 13)),
        Text(value,
            style: TextStyle(
                color: colors.textPrimary,
                fontSize: 13,
                fontWeight: FontWeight.w600)),
      ],
    );
  }
}

class ManualDepositProofScreen extends StatefulWidget {
  final Map<String, dynamic> payload;

  const ManualDepositProofScreen({super.key, required this.payload});

  @override
  State<ManualDepositProofScreen> createState() => _ManualDepositProofScreenState();
}

class _ManualDepositProofScreenState extends State<ManualDepositProofScreen> {
  final _formKey = GlobalKey<FormState>();
  final _controllers = <String, TextEditingController>{};
  final _fieldTypes = <String, String>{};
  final _requiredFields = <String>{};
  final _picker = ImagePicker();
  String? _proofPath;
  bool _submitting = false;

  @override
  void initState() {
    super.initState();
    for (final field in _parseFieldOptions(widget.payload['field_options'])) {
      _fieldTypes[field.key] = field.type;
      if (_isRequiredValidation(field.validation)) {
        _requiredFields.add(field.key);
      }
      if (_isFileType(field.type)) continue;
      _controllers[field.key] = TextEditingController();
    }
  }

  @override
  void dispose() {
    for (final controller in _controllers.values) {
      controller.dispose();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final tr = context.tr;
    final colors = context.colors;
    final tnx = (widget.payload['tnx'] ?? '').toString();
    final currency = (widget.payload['currency'] ?? '').toString();
    final amount = _asDouble(widget.payload['amount']);
    final charge = _asDouble(widget.payload['charge']);
    final finalAmount = _asDouble(widget.payload['final_amount']);
    final payAmount = _asDouble(widget.payload['pay_amount']);
    final details = _stripHtml((widget.payload['payment_details'] ?? '').toString());
    final message = (widget.payload['message'] ?? tr('deposit_created_submit_proof')).toString();
    final fields = _parseFieldOptions(widget.payload['field_options']);

    return Scaffold(
      backgroundColor: colors.bgDark,
      appBar: AppBar(title: Text(tr('manual_deposit'))),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(message,
                  style: TextStyle(color: colors.textSecondary, fontSize: 13)),
              const SizedBox(height: 14),
              _summaryCard(
                context,
                rows: [
                  _SummaryRow(tr('transaction_id'), tnx),
                  _SummaryRow(tr('amount'), '\$${amount.toStringAsFixed(2)}'),
                  _SummaryRow(tr('charge'), '\$${charge.toStringAsFixed(2)}'),
                  _SummaryRow(tr('final_amount'), '\$${finalAmount.toStringAsFixed(2)}'),
                  _SummaryRow(tr('pay_amount'), '${payAmount.toStringAsFixed(2)} $currency'),
                ],
              ),
              const SizedBox(height: 14),
              Text(tr('payment_details'),
                  style: TextStyle(
                    color: colors.textPrimary,
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                  )),
              const SizedBox(height: 8),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: colors.surface,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  details.isEmpty ? '-' : details,
                  style: TextStyle(color: colors.textSecondary, height: 1.4),
                ),
              ),
              if (fields.isNotEmpty) ...[
                const SizedBox(height: 16),
                ...fields.map((field) => _buildDynamicField(context, field)),
              ],
              const SizedBox(height: 16),
              AppButton(
                label: tr('submit_proof'),
                isLoading: _submitting,
                onTap: () => _submit(tnx),
                icon: Icons.check_circle_outline,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDynamicField(BuildContext context, _ManualField field) {
    final tr = context.tr;
    final isRequired = _isRequiredValidation(field.validation);

    if (_isFileType(field.type)) {
      return Padding(
        padding: const EdgeInsets.only(bottom: 12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            AppButton(
              label: _proofPath == null ? field.name : tr('change_file'),
              outlined: true,
              icon: Icons.upload_file,
              onTap: _pickProof,
            ),
            if (_proofPath != null) ...[
              const SizedBox(height: 6),
              Text(
                _proofPath!.split('\\').last,
                  style: TextStyle(color: context.colors.textSecondary, fontSize: 12),
              ),
            ],
            if (isRequired && _proofPath == null)
              Padding(
                padding: const EdgeInsets.only(top: 6),
                child: Text(
                  tr('file_required'),
                  style: const TextStyle(color: AppTheme.error, fontSize: 12),
                ),
              ),
          ],
        ),
      );
    }

    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: AppTextField(
        label: field.name,
        controller: _controllers[field.key],
        keyboardType: _keyboardTypeFor(field.type),
        maxLines: _isMultilineType(field.type) ? 4 : 1,
        validator: isRequired
            ? (v) => (v ?? '').trim().isEmpty ? tr('field_required') : null
            : null,
      ),
    );
  }

  Future<void> _pickProof() async {
    final file = await _picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
    if (file == null || !mounted) return;
    setState(() => _proofPath = file.path);
  }

  Future<void> _submit(String tnx) async {
    if (_submitting) return;
    if (!(_formKey.currentState?.validate() ?? false)) return;

    final hasRequiredFile = !_requiredFields.any(
      (key) => _fieldTypes[key] == 'file' && (_proofPath == null || _proofPath!.isEmpty),
    );
    if (!hasRequiredFile) {
      setState(() {});
      return;
    }

    final manualFields = <String, String>{};
    _controllers.forEach((key, controller) {
      final value = controller.text.trim();
      if (value.isNotEmpty) {
        manualFields[key] = value;
      }
    });

    setState(() => _submitting = true);
    try {
      await DepositService.submitManualProof(
        tnx: tnx,
        proof: _proofPath,
        manualFields: manualFields,
      );

      if (!mounted) return;
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(context.tr('proof_submitted_review')),
          backgroundColor: AppTheme.success,
        ),
      );
    } catch (e) {
      if (!mounted) return;
      setState(() => _submitting = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e.toString()),
          backgroundColor: AppTheme.error,
        ),
      );
    }
  }

  Widget _summaryCard(BuildContext context, {required List<_SummaryRow> rows}) {
    final colors = context.colors;
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: colors.surface,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        children: rows
            .map(
              (row) => Padding(
                padding: const EdgeInsets.symmetric(vertical: 4),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(row.label,
                        style: TextStyle(color: colors.textSecondary, fontSize: 13)),
                    Flexible(
                      child: Text(
                        row.value,
                        textAlign: TextAlign.right,
                        style: TextStyle(
                          color: colors.textPrimary,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            )
            .toList(),
      ),
    );
  }

  List<_ManualField> _parseFieldOptions(dynamic raw) {
    dynamic source = raw;
    if (source is String && source.isNotEmpty) {
      try {
        source = jsonDecode(source);
      } catch (_) {
        source = null;
      }
    }
    final fields = <_ManualField>[];

    if (source is Map) {
      source.forEach((key, value) {
        if (value is! Map) return;
        final option = Map<String, dynamic>.from(value);
        option.putIfAbsent('key', () => key.toString());
        final field = _fieldFromOption(option, fields.length);
        if (field != null) {
          fields.add(field);
        }
      });
    } else if (source is List) {
      for (var i = 0; i < source.length; i++) {
        final value = source[i];
        if (value is! Map) continue;
        final field = _fieldFromOption(Map<String, dynamic>.from(value), i);
        if (field != null) {
          fields.add(field);
        }
      }
    }

    return fields;
  }

  double _asDouble(dynamic value) {
    if (value is num) return value.toDouble();
    return double.tryParse(value?.toString() ?? '') ?? 0;
    }

  String _stripHtml(String html) {
    return html
        .replaceAll(RegExp(r'<\s*br\s*/?>', caseSensitive: false), '\n')
        .replaceAll(RegExp(r'</li\s*>', caseSensitive: false), '\n')
        .replaceAll(RegExp(r'<li\b[^>]*>', caseSensitive: false), '- ')
        .replaceAll(RegExp(r'</p\s*>', caseSensitive: false), '\n')
        .replaceAll(RegExp(r'</div\s*>', caseSensitive: false), '\n')
        .replaceAll(RegExp(r'<[^>]*>'), '')
        .replaceAll('&nbsp;', ' ')
        .replaceAll('&amp;', '&')
        .replaceAll('&lt;', '<')
        .replaceAll('&gt;', '>')
        .replaceAll('&quot;', '"')
        .replaceAll('&#39;', "'")
        .replaceAll(RegExp(r'\n\s*\n+'), '\n\n')
        .trim();
  }

  _ManualField? _fieldFromOption(Map<String, dynamic> option, int index) {
    final rawName = (option['name'] ?? option['label'] ?? '').toString().trim();
    final fallbackName = rawName.isEmpty ? 'Field ${index + 1}' : rawName;
    final key = _fieldKey(option, fallbackName, index);

    return _ManualField(
      key: key,
      name: fallbackName,
      type: _normalizeType((option['type'] ?? 'text').toString()),
      validation: (option['validation'] ?? 'nullable').toString().toLowerCase(),
    );
  }

  String _fieldKey(Map<String, dynamic> option, String fallbackName, int index) {
    final candidates = [
      option['key'],
      option['slug'],
      option['field'],
      option['field_name'],
      option['name'],
    ];

    for (final candidate in candidates) {
      final value = candidate?.toString().trim() ?? '';
      if (value.isNotEmpty) {
        return _sanitizeFieldKey(value);
      }
    }

    return '${_sanitizeFieldKey(fallbackName)}_${index + 1}';
  }

  String _sanitizeFieldKey(String value) {
    var normalized = value.toLowerCase().replaceAll(RegExp(r'[^a-z0-9]+'), '_');
    while (normalized.startsWith('_')) {
      normalized = normalized.substring(1);
    }
    while (normalized.endsWith('_')) {
      normalized = normalized.substring(0, normalized.length - 1);
    }
    return normalized.isEmpty ? 'field' : normalized;
  }

  bool _isFileType(String type) {
    return {'file', 'image', 'document'}.contains(type);
  }

  bool _isMultilineType(String type) {
    return type == 'textarea';
  }

  bool _isRequiredValidation(String validation) {
    return validation.toLowerCase() == 'required';
  }

  String _normalizeType(String type) {
    final normalized = type.toLowerCase();
    if (normalized == 'number' || normalized == 'numeric') return 'number';
    if (normalized == 'phone' || normalized == 'tel') return 'phone';
    if (normalized == 'file' || normalized == 'image' || normalized == 'document') return 'file';
    if (normalized == 'textarea') return 'textarea';
    if (normalized == 'email') return 'email';
    return 'text';
  }

  TextInputType _keyboardTypeFor(String type) {
    switch (type) {
      case 'number':
        return const TextInputType.numberWithOptions(decimal: true);
      case 'email':
        return TextInputType.emailAddress;
      case 'phone':
        return TextInputType.phone;
      case 'textarea':
        return TextInputType.multiline;
      default:
        return TextInputType.text;
    }
  }
}

class _ManualField {
  final String key;
  final String name;
  final String type;
  final String validation;

  const _ManualField({
    required this.key,
    required this.name,
    required this.type,
    required this.validation,
  });
}

class _SummaryRow {
  final String label;
  final String value;

  const _SummaryRow(this.label, this.value);
}

// ── Payment WebView ────────────────────────────────────────────────────────────
class PaymentWebView extends StatefulWidget {
  final String url;
  final String tnx;

  const PaymentWebView({super.key, required this.url, required this.tnx});

  @override
  State<PaymentWebView> createState() => _PaymentWebViewState();
}

class _PaymentWebViewState extends State<PaymentWebView> {
  late final WebViewController _controller;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(NavigationDelegate(
        onPageStarted: (_) => setState(() => _isLoading = true),
        onPageFinished: (_) => setState(() => _isLoading = false),
      ))
      ..loadRequest(Uri.parse(widget.url));
  }

  @override
  Widget build(BuildContext context) {
    final tr = context.tr;
    final colors = context.colors;
    return Scaffold(
      appBar: AppBar(
        title: Text(tr('complete_payment')),
        actions: [
          TextButton(
            onPressed: () => _checkStatus(),
            child: Text(tr('done'), style: TextStyle(color: colors.primary)),
          ),
        ],
      ),
      body: Stack(
        children: [
          WebViewWidget(controller: _controller),
          if (_isLoading)
            Container(
              color: colors.bgDark,
              child: Center(
                child: CircularProgressIndicator(color: colors.primary),
              ),
            ),
        ],
      ),
    );
  }

  Future<void> _checkStatus() async {
    try {
      final status = await DepositService.getDepositStatus(widget.tnx);
      final txnStatus = status['txn_status'] ?? 'pending';
      if (!mounted) return;
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(txnStatus == 'success'
              ? context.tr('payment_confirmed')
              : '${context.tr('payment_status')}: $txnStatus'),
          backgroundColor: txnStatus == 'success'
              ? AppTheme.success
              : AppTheme.warning,
        ),
      );
    } catch (_) {
      if (mounted) Navigator.pop(context);
    }
  }
}

