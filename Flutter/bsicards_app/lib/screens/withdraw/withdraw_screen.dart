import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';

import '../../config/app_colors.dart';
import '../../config/app_theme.dart';
import '../../l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../../services/withdraw_service.dart';
import '../../widgets/common_widgets.dart';

class WithdrawScreen extends StatefulWidget {
  const WithdrawScreen({super.key});

  @override
  State<WithdrawScreen> createState() => _WithdrawScreenState();
}

class _WithdrawScreenState extends State<WithdrawScreen> {
  final _amountCtrl = TextEditingController();

  List<Map<String, dynamic>> _methods = [];
  List<Map<String, dynamic>> _accounts = [];
  Map<String, dynamic>? _selectedAccount;
  Map<String, dynamic>? _preview;

  bool _loading = true;
  bool _loadingPreview = false;
  bool _processing = false;
  bool _deletingAccount = false;

  @override
  void initState() {
    super.initState();
    _bootstrap();
  }

  @override
  void dispose() {
    _amountCtrl.dispose();
    super.dispose();
  }

  Future<void> _bootstrap() async {
    setState(() => _loading = true);
    try {
      final methods = await WithdrawService.getMethods();
      final accounts = await WithdrawService.getAccounts();
      if (!mounted) return;

      setState(() {
        _methods = methods;
        _accounts = accounts;
        _selectedAccount = accounts.isNotEmpty ? accounts.first : null;
        _loading = false;
      });

      await _refreshPreview();
    } catch (e) {
      if (!mounted) return;
      setState(() => _loading = false);
      _showSnack(e.toString(), isError: true);
    }
  }

  Future<void> _refreshPreview() async {
    final accountId = _selectedAccount?['id'];
    if (accountId == null) {
      setState(() => _preview = null);
      return;
    }

    final amount = double.tryParse(_amountCtrl.text.trim());
    if (amount == null || amount <= 0) {
      if (mounted) {
        setState(() {
          _preview = null;
          _loadingPreview = false;
        });
      }
      return;
    }

    setState(() => _loadingPreview = true);
    try {
      final data = await WithdrawService.getDetails(
        withdrawAccountId: accountId as int,
        amount: amount,
      );
      if (!mounted) return;
      setState(() {
        _preview = data;
        _loadingPreview = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _loadingPreview = false);
    }
  }

  Future<void> _submit() async {
    final tr = context.tr;
    final accountId = _selectedAccount?['id'];
    final amount = double.tryParse(_amountCtrl.text.trim());

    if (accountId == null) {
      _showSnack(tr('withdraw_account_required'), isError: true);
      return;
    }
    if (amount == null || amount <= 0) {
      _showSnack(tr('enter_valid_amount'), isError: true);
      return;
    }

    setState(() => _processing = true);
    try {
      final res = await WithdrawService.initiate(
        withdrawAccountId: accountId as int,
        amount: amount,
      );
      if (!mounted) return;

      setState(() => _processing = false);
      await context.read<AuthProvider>().refreshUser();
      await _bootstrap();

      final tnx = (res['tnx'] ?? '-').toString();
      final msg = (res['message'] ?? tr('withdraw_request_submitted')).toString();

      if (!mounted) return;
      showDialog<void>(
        context: context,
        builder: (ctx) => AlertDialog(
          title: Text(tr('withdraw')),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(msg),
              const SizedBox(height: 8),
              Text('${tr('transaction_id')}: $tnx'),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: Text(tr('done')),
            ),
          ],
        ),
      );
    } catch (e) {
      if (!mounted) return;
      setState(() => _processing = false);
      _showSnack(e.toString(), isError: true);
    }
  }

  Future<void> _showAddAccountSheet() async {
    final result = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: context.colors.bgCard,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (_) => _AddAccountSheet(methods: _methods),
    );

    // Only refresh after the sheet is fully gone from the tree
    if (result == true && mounted) {
      _showSnack(context.tr('withdraw_account_created_successfully'), isError: false);
      await _bootstrap();
    }
  }

  Future<void> _showEditAccountSheet() async {
    final account = _selectedAccount;
    if (account == null) return;

    final result = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: context.colors.bgCard,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (_) => _AddAccountSheet(
        methods: _methods,
        initialAccount: account,
      ),
    );

    if (result == true && mounted) {
      _showSnack(context.tr('withdraw_account_updated_successfully'), isError: false);
      await _bootstrap();
    }
  }

  Future<void> _deleteSelectedAccount() async {
    final account = _selectedAccount;
    if (account == null || _deletingAccount) return;

    final accountName = (account['method_name'] ?? context.tr('this_account')).toString();
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(context.tr('confirm')),
        content: Text('${context.tr('delete')} $accountName?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: Text(context.tr('cancel')),
          ),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: Text(
              context.tr('delete'),
              style: const TextStyle(color: AppTheme.error),
            ),
          ),
        ],
      ),
    );

    if (confirmed != true || !mounted) return;

    setState(() => _deletingAccount = true);
    try {
      await WithdrawService.deleteAccount(account['id'] as int);
      if (!mounted) return;
      _showSnack(context.tr('withdraw_account_deleted_successfully'), isError: false);
      await _bootstrap();
    } catch (e) {
      if (!mounted) return;
      _showSnack(e.toString(), isError: true);
    } finally {
      if (mounted) setState(() => _deletingAccount = false);
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
    final enteredAmount = double.tryParse(_amountCtrl.text.trim());
    final showPreview = enteredAmount != null && enteredAmount > 0;

    return Scaffold(
      backgroundColor: colors.bgDark,
      appBar: AppBar(
        title: Text(tr('withdraw')),
        actions: [
          IconButton(
            onPressed: _methods.isEmpty ? null : _showAddAccountSheet,
            icon: const Icon(Icons.add_circle_outline),
            tooltip: tr('add_withdraw_account'),
          ),
        ],
      ),
      body: _loading
          ? Center(child: CircularProgressIndicator(color: colors.primary))
          : RefreshIndicator(
              onRefresh: _bootstrap,
              color: colors.primary,
              backgroundColor: colors.bgCard,
              child: ListView(
                padding: const EdgeInsets.all(20),
                children: [
                  if (_accounts.isEmpty)
                    Container(
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        color: colors.bgCard,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: colors.divider),
                      ),
                      child: Column(
                        children: [
                          Icon(Icons.account_balance_wallet_outlined,
                              size: 40, color: colors.textSecondary),
                          const SizedBox(height: 12),
                          Text(
                            tr('no_withdraw_accounts_found'),
                            style: TextStyle(
                              color: colors.textPrimary,
                              fontWeight: FontWeight.w700,
                              fontSize: 16,
                            ),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            tr('create_withdraw_account_to_continue'),
                            textAlign: TextAlign.center,
                            style: TextStyle(color: colors.textSecondary),
                          ),
                          const SizedBox(height: 14),
                          AppButton(
                            label: tr('add_withdraw_account'),
                            icon: Icons.add,
                            onTap: _methods.isEmpty ? null : _showAddAccountSheet,
                          ),
                        ],
                      ),
                    )
                  else ...[
                    Row(
                      children: [
                        Expanded(
                          child: DropdownButtonFormField<Map<String, dynamic>>(
                            value: _selectedAccount,
                            decoration: InputDecoration(
                              labelText: tr('withdraw_account'),
                              filled: true,
                              fillColor: colors.bgCard,
                            ),
                            items: _accounts
                                .map(
                                  (account) => DropdownMenuItem<Map<String, dynamic>>(
                                    value: account,
                                    child: Text((account['method_name'] ?? '').toString()),
                                  ),
                                )
                                .toList(),
                            onChanged: (value) {
                              setState(() => _selectedAccount = value);
                              _refreshPreview();
                            },
                          ),
                        ),
                        const SizedBox(width: 10),
                        Container(
                          decoration: BoxDecoration(
                            color: colors.bgCard,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: colors.divider),
                          ),
                          child: IconButton(
                            tooltip: tr('edit_account'),
                            onPressed: _deletingAccount ? null : _showEditAccountSheet,
                            icon: Icon(Icons.edit_outlined, color: colors.primary),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Container(
                          decoration: BoxDecoration(
                            color: colors.bgCard,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: colors.divider),
                          ),
                          child: IconButton(
                            tooltip: tr('delete_account'),
                            onPressed: _deletingAccount ? null : _deleteSelectedAccount,
                            icon: _deletingAccount
                                ? SizedBox(
                                    width: 18,
                                    height: 18,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2,
                                      color: colors.primary,
                                    ),
                                  )
                                : const Icon(Icons.delete_outline, color: AppTheme.error),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),
                    AppTextField(
                      label: tr('enter_amount'),
                      controller: _amountCtrl,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true),
                      onChanged: (_) => _refreshPreview(),
                    ),
                    if (showPreview) ...[
                      const SizedBox(height: 14),
                      Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: colors.bgCard,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: colors.divider),
                        ),
                        child: _loadingPreview
                            ? Center(
                                child: Padding(
                                  padding: const EdgeInsets.all(18),
                                  child: CircularProgressIndicator(color: colors.primary),
                                ),
                              )
                            : Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  SectionHeader(title: tr('withdraw_preview')),
                                  const SizedBox(height: 8),
                                  InfoTile(
                                    label: tr('charge'),
                                    value: (_preview?['charge'] ?? 0).toString(),
                                    icon: Icons.attach_money,
                                  ),
                                  InfoTile(
                                    label: tr('final_amount'),
                                    value: (_preview?['total_amount'] ?? 0).toString(),
                                    icon: Icons.calculate_outlined,
                                  ),
                                  InfoTile(
                                    label: tr('pay_amount'),
                                    value: (_preview?['pay_amount'] ?? 0).toString(),
                                    icon: Icons.swap_horiz,
                                  ),
                                  InfoTile(
                                    label: tr('processing_time'),
                                    value: (_preview?['processing_time'] ?? '-').toString(),
                                    icon: Icons.timelapse,
                                  ),
                                ],
                              ),
                      ),
                      const SizedBox(height: 16),
                    ],
                    if (!showPreview) const SizedBox(height: 16),
                    AppButton(
                      label: tr('submit_withdraw_request'),
                      icon: Icons.arrow_downward_rounded,
                      isLoading: _processing,
                      onTap: _processing ? null : _submit,
                    ),
                  ],
                ],
              ),
            ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Add Withdraw Account – dedicated StatefulWidget so controllers are properly
// lifecycle-managed (no use-after-dispose crashes).
// ─────────────────────────────────────────────────────────────────────────────
class _AddAccountSheet extends StatefulWidget {
  final List<Map<String, dynamic>> methods;
  final Map<String, dynamic>? initialAccount;

  const _AddAccountSheet({
    required this.methods,
    this.initialAccount,
  });

  @override
  State<_AddAccountSheet> createState() => _AddAccountSheetState();
}

class _AddAccountSheetState extends State<_AddAccountSheet> {
  final _formKey = GlobalKey<FormState>();
  final _methodNameCtrl = TextEditingController();

  Map<String, dynamic>? _selectedMethod;
  final Map<String, TextEditingController> _valueCtrls = {};
  final Map<String, String> _filePaths = {};
  bool _submitting = false;

  bool get _isEdit => widget.initialAccount != null;

  @override
  void initState() {
    super.initState();
    final initialMethodId = widget.initialAccount?['withdraw_method_id'];
    if (widget.methods.isNotEmpty) {
      _selectedMethod = widget.methods.firstWhere(
        (m) => m['id'] == initialMethodId,
        orElse: () => widget.methods.first,
      );
      _rebuildControllers();
      _prefillFromExisting();
    }
  }

  @override
  void dispose() {
    _methodNameCtrl.dispose();
    for (final c in _valueCtrls.values) {
      c.dispose();
    }
    super.dispose();
  }

  // Dispose previous controllers BEFORE clearing so no orphaned listeners remain.
  void _rebuildControllers() {
    for (final c in _valueCtrls.values) {
      c.dispose();
    }
    _valueCtrls.clear();
    _filePaths.clear();

    for (final field in _selectedFields()) {
      final name = _fieldKey(field);
      if (name.isEmpty) continue;
      final type = (field['type'] ?? 'text').toString().toLowerCase();
      if (type == 'file') {
        _filePaths[name] = '';
      } else {
        _valueCtrls[name] = TextEditingController();
      }
    }

    if (_selectedMethod != null && _methodNameCtrl.text.trim().isEmpty) {
      _methodNameCtrl.text =
          '${_selectedMethod!['name']}-${_selectedMethod!['currency']}';
    }
  }

  void _prefillFromExisting() {
    final account = widget.initialAccount;
    if (account == null) return;

    final existingName = (account['method_name'] ?? '').toString().trim();
    if (existingName.isNotEmpty) {
      _methodNameCtrl.text = existingName;
    }

    final rawCredentials = account['credentials'];
    if (rawCredentials is! Map<String, dynamic>) return;

    for (final field in _selectedFields()) {
      final key = _fieldKey(field);
      if (key.isEmpty) continue;

      final type = (field['type'] ?? 'text').toString().toLowerCase();
      final stored = rawCredentials[key];
      final storedValue = stored is Map<String, dynamic>
          ? (stored['value'] ?? '').toString()
          : (stored ?? '').toString();

      if (type == 'file') {
        _filePaths[key] = storedValue;
      } else {
        _valueCtrls[key]?.text = storedValue;
      }
    }
  }

  List<Map<String, dynamic>> _selectedFields() {
    final fields = _selectedMethod?['fields'];
    if (fields is List) {
      return fields.whereType<Map<String, dynamic>>().toList();
    }
    return [];
  }

  String _fieldKey(Map<String, dynamic> field) {
    final label = (field['label'] ?? '').toString().trim();
    if (label.isNotEmpty) return label;
    return (field['name'] ?? '').toString().trim();
  }

  String _fieldValidation(Map<String, dynamic> field) {
    final raw = (field['validation'] ?? 'required').toString().trim().toLowerCase();
    return raw == 'required' ? 'required' : 'nullable';
  }

  Future<void> _pickFile(String name) async {
    final picked =
        await ImagePicker().pickImage(source: ImageSource.gallery);
    if (picked == null) return;
    if (!mounted) return;
    setState(() => _filePaths[name] = picked.path);
  }

  Future<void> _submit() async {
    if (!(_formKey.currentState?.validate() ?? false)) return;
    if (_selectedMethod == null) return;

    for (final field in _selectedFields()) {
      final name = _fieldKey(field);
      final validation = _fieldValidation(field);
      final type = (field['type'] ?? 'text').toString().toLowerCase();
      if (validation != 'required') continue;

      if (type == 'file') {
        if ((_filePaths[name] ?? '').trim().isEmpty) {
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content: Text('$name is required.'),
            backgroundColor: AppTheme.error,
          ));
          return;
        }
      } else {
        if ((_valueCtrls[name]?.text.trim() ?? '').isEmpty) {
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content: Text('$name is required.'),
            backgroundColor: AppTheme.error,
          ));
          return;
        }
      }
    }

    setState(() => _submitting = true);
    try {
      final credentialsPayload = <String, Map<String, dynamic>>{};
      for (final field in _selectedFields()) {
        final key = _fieldKey(field);
        if (key.isEmpty) continue;

        final type = (field['type'] ?? 'text').toString().toLowerCase();
        final validation = _fieldValidation(field);
        final value = type == 'file'
            ? (_filePaths[key] ?? '')
            : (_valueCtrls[key]?.text.trim() ?? '');

        credentialsPayload[key] = {
          'type': type,
          'validation': validation,
          'value': value,
        };
      }

      if (_isEdit) {
        await WithdrawService.updateAccount(
          accountId: widget.initialAccount!['id'] as int,
          withdrawMethodId: _selectedMethod!['id'] as int,
          methodName: _methodNameCtrl.text.trim(),
          credentials: credentialsPayload,
          filePaths: _filePaths,
        );
      } else {
        await WithdrawService.createAccount(
          withdrawMethodId: _selectedMethod!['id'] as int,
          methodName: _methodNameCtrl.text.trim(),
          credentials: credentialsPayload,
          filePaths: _filePaths,
        );
      }
      if (!mounted) return;
      // Pop with true so the parent knows to refresh
      Navigator.pop(context, true);
    } catch (e) {
      if (!mounted) return;
      setState(() => _submitting = false);
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(e.toString()),
        backgroundColor: AppTheme.error,
      ));
    }
  }

  Widget _buildCredentialField(Map<String, dynamic> field) {
    final name = _fieldKey(field);
    final type = (field['type'] ?? 'text').toString().toLowerCase();
    final isRequired = _fieldValidation(field) == 'required';
    final colors = context.colors;

    if (type == 'file') {
      final path = _filePaths[name] ?? '';
      return Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(12),
          color: colors.surfaceLight,
          border: Border.all(color: colors.divider),
        ),
        child: Row(
          children: [
            Expanded(
              child: Text(
                path.isEmpty
                    ? '$name${isRequired ? ' *' : ''}'
                    : path.split('/').last,
                style: TextStyle(color: colors.textPrimary),
                overflow: TextOverflow.ellipsis,
              ),
            ),
            const SizedBox(width: 10),
            OutlinedButton.icon(
              onPressed: () => _pickFile(name),
              icon: const Icon(Icons.upload_file),
              label: Text(
                  path.isEmpty ? context.tr('pick_file') : context.tr('change_file')),
            ),
          ],
        ),
      );
    }

    return AppTextField(
      label: '$name${isRequired ? ' *' : ''}',
      controller: _valueCtrls[name],
      maxLines: type == 'textarea' ? 3 : 1,
      validator: isRequired
          ? (v) => (v == null || v.trim().isEmpty)
              ? context.tr('field_required')
              : null
          : null,
    );
  }

  @override
  Widget build(BuildContext context) {
    final tr = context.tr;
    final colors = context.colors;

    return SafeArea(
      child: Padding(
        padding: EdgeInsets.only(
          left: 20,
          right: 20,
          top: 16,
          bottom: MediaQuery.of(context).viewInsets.bottom + 20,
        ),
        child: Form(
          key: _formKey,
          child: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // ── Header ──────────────────────────────────────────
                Text(
                  _isEdit ? tr('edit_withdraw_account') : tr('add_withdraw_account'),
                  style: TextStyle(
                    color: colors.textPrimary,
                    fontSize: 18,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 14),
                // ── Method picker ────────────────────────────────────
                DropdownButtonFormField<Map<String, dynamic>>(
                  value: _selectedMethod,
                  decoration: InputDecoration(
                      labelText: tr('select_payment_method')),
                  items: widget.methods
                      .map((m) => DropdownMenuItem<Map<String, dynamic>>(
                            value: m,
                            child: Text('${m['name']} (${m['currency']})'),
                          ))
                      .toList(),
                  onChanged: _submitting
                      ? null
                      : (value) {
                          setState(() {
                            _selectedMethod = value;
                            _methodNameCtrl.clear();
                            _rebuildControllers();
                          });
                        },
                ),
                const SizedBox(height: 12),
                // ── Method name ──────────────────────────────────────
                AppTextField(
                  label: tr('account_label'),
                  controller: _methodNameCtrl,
                  validator: (v) => (v ?? '').trim().isEmpty
                      ? tr('field_required')
                      : null,
                ),
                const SizedBox(height: 12),
                // ── Dynamic credential fields ────────────────────────
                for (final field in _selectedFields()) ...[
                  _buildCredentialField(field),
                  const SizedBox(height: 10),
                ],
                const SizedBox(height: 8),
                // ── Submit ───────────────────────────────────────────
                AppButton(
                  label: _isEdit ? tr('save_changes') : tr('create_account'),
                  icon: Icons.save_outlined,
                  isLoading: _submitting,
                  onTap: _submitting ? null : _submit,
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

