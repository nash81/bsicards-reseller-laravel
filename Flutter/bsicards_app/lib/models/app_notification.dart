 class AppNotification {
  final int id;
  final String title;
  final String icon;
  final String? actionUrl;
  final bool isRead;
  final String createdAt;
  final String timeAgo;

  const AppNotification({
    required this.id,
    required this.title,
    required this.icon,
    this.actionUrl,
    required this.isRead,
    required this.createdAt,
    required this.timeAgo,
  });

  factory AppNotification.fromJson(Map<String, dynamic> json) {
    return AppNotification(
      id: (json['id'] ?? 0) as int,
      title: (json['title'] ?? '').toString(),
      icon: (json['icon'] ?? 'bell').toString(),
      actionUrl: json['action_url']?.toString(),
      isRead: json['read'] == true,
      createdAt: (json['created_at'] ?? '').toString(),
      timeAgo: (json['time_ago'] ?? '').toString(),
    );
  }
}

