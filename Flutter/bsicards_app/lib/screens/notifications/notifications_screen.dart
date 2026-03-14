import 'package:flutter/material.dart';
import '../../config/app_colors.dart';
import '../../config/app_theme.dart';
import '../../l10n/app_localizations.dart';
import '../../models/app_notification.dart';
import '../../services/notification_service.dart';
import '../../widgets/common_widgets.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  List<AppNotification> _items = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final items = await NotificationService.getNotifications();
      if (!mounted) return;
      setState(() {
        _items = items;
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString();
        _loading = false;
      });
    }
  }

  IconData _iconFor(String icon) {
    switch (icon.toLowerCase()) {
      case 'wallet':
      case 'wallet-2':
        return Icons.account_balance_wallet_outlined;
      case 'shield':
      case 'security':
        return Icons.security_outlined;
      case 'clock':
      case 'hourglass':
        return Icons.hourglass_bottom;
      case 'credit-card':
      case 'card':
        return Icons.credit_card_outlined;
      default:
        return Icons.notifications_outlined;
    }
  }

  @override
  Widget build(BuildContext context) {
    final tr = context.tr;
    final colors = context.colors;

    return Scaffold(
      backgroundColor: colors.bgDark,
      appBar: AppBar(title: Text(tr('notifications'))),
      body: RefreshIndicator(
        onRefresh: _load,
        color: colors.primary,
        backgroundColor: colors.bgCard,
        child: _loading
            ? ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16),
                children: [Center(child: CircularProgressIndicator(color: colors.primary))],
              )
            : _error != null
                ? ListView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(24),
                    children: [
                      Center(
                        child: Column(
                          children: [
                            const Icon(Icons.error_outline, color: Colors.redAccent, size: 36),
                            const SizedBox(height: 12),
                            Text(_error!, style: const TextStyle(color: Colors.redAccent), textAlign: TextAlign.center),
                            const SizedBox(height: 12),
                            TextButton(onPressed: _load, child: Text(tr('retry'))),
                          ],
                        ),
                      ),
                    ],
                  )
                : _items.isEmpty
                    ? ListView(
                        physics: const AlwaysScrollableScrollPhysics(),
                        children: [
                          EmptyState(
                            icon: Icons.notifications_off_outlined,
                            title: tr('no_notifications'),
                          ),
                        ],
                      )
                    : ListView.separated(
                        physics: const AlwaysScrollableScrollPhysics(),
                        padding: const EdgeInsets.all(16),
                        itemCount: _items.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 10),
                        itemBuilder: (_, i) {
                          final n = _items[i];
                          final iconColor = n.isRead ? colors.textSecondary : colors.primary;

                          return InkWell(
                            borderRadius: BorderRadius.circular(14),
                            onTap: () async {
                              if (!n.isRead) {
                                await NotificationService.markNotificationRead(n.id);
                                if (!mounted) return;
                                setState(() {
                                  _items[i] = AppNotification(
                                    id: n.id,
                                    title: n.title,
                                    icon: n.icon,
                                    actionUrl: n.actionUrl,
                                    isRead: true,
                                    createdAt: n.createdAt,
                                    timeAgo: n.timeAgo,
                                  );
                                });
                              }
                            },
                            child: Container(
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: colors.bgCard,
                                borderRadius: BorderRadius.circular(14),
                                border: Border.all(color: colors.divider),
                              ),
                              child: Row(
                                children: [
                                  Container(
                                    width: 40,
                                    height: 40,
                                    decoration: BoxDecoration(
                                      color: iconColor.withValues(alpha: 0.14),
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                    child: Icon(_iconFor(n.icon), color: iconColor),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Text(
                                      n.title,
                                      style: TextStyle(
                                        color: colors.textPrimary,
                                        fontWeight: n.isRead ? FontWeight.w500 : FontWeight.w700,
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Text(
                                    n.timeAgo.isEmpty ? n.createdAt : n.timeAgo,
                                    style: TextStyle(color: colors.textSecondary, fontSize: 11),
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
      ),
    );
  }
}

