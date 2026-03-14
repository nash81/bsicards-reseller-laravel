import '../config/app_config.dart';
import '../models/app_notification.dart';
import 'api_service.dart';

class NotificationService {
  static Future<List<AppNotification>> getNotifications({int limit = 20}) async {
    final data = await ApiService.get(
      AppConfig.notificationsEndpoint,
      params: {'limit': limit},
    );

    return (data['data'] as List? ?? [])
        .map((e) => AppNotification.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  static Future<void> markNotificationRead(int id) async {
    await ApiService.post(AppConfig.markNotificationReadEndpoint(id), body: {});
  }

  static Future<void> markAllAsRead() async {
    await ApiService.post(AppConfig.markAllNotificationsReadEndpoint, body: {});
  }
}

