import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import '../l10n/app_localizations.dart';
import '../models/virtual_card.dart';
import '../config/app_theme.dart';

class CardWidget extends StatelessWidget {
  final VirtualCard card;
  final VoidCallback? onTap;
  final bool compact;
  final bool showFullNumber;

  const CardWidget({
    super.key,
    required this.card,
    this.onTap,
    this.compact = false,
    this.showFullNumber = false,
  });

  @override
  Widget build(BuildContext context) {
    final tr = context.tr;
    return GestureDetector(
      onTap: onTap,
      child: Container(
        height: compact ? 120 : 200,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(20),
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [card.cardGradientStart, card.cardGradientEnd],
          ),
          boxShadow: [
            BoxShadow(
              color: card.cardGradientStart.withValues(alpha: 0.4),
              blurRadius: 20,
              offset: const Offset(0, 8),
            ),
          ],
        ),
        child: Stack(
          children: [
            // Background circles decoration
            Positioned(
              right: -30,
              top: -30,
              child: Container(
                width: 160,
                height: 160,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: Colors.white.withValues(alpha: 0.05),
                ),
              ),
            ),
            Positioned(
              right: 40,
              bottom: -20,
              child: Container(
                width: 100,
                height: 100,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: Colors.white.withValues(alpha: 0.05),
                ),
              ),
            ),
            // Card content
            Padding(
              padding: EdgeInsets.all(compact ? 16 : 22),
              child: compact
                  ? _compactContent(context)
                  : _fullContent(context),
            ),
            // Blocked overlay
            if (card.isBlocked)
              Positioned.fill(
                child: Container(
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(20),
                    color: Colors.black.withValues(alpha: 0.5),
                  ),
                  child: Center(
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.lock, color: Colors.white, size: 20),
                        const SizedBox(width: 8),
                        Text(tr('blocked').toUpperCase(),
                            style: TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                                letterSpacing: 2)),
                      ],
                    ),
                  ),
                ),
              ),
          ],
        ),
      ),
    ).animate().fadeIn(duration: 400.ms).slideY(begin: 0.1);
  }

  Widget _fullContent(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            _cardTypeBadge(context),
            _networkLogo(),
          ],
        ),
        const Spacer(),
        Align(
          alignment: Alignment.center,
          child: Text(
            showFullNumber ? card.fullNumber : card.maskedNumber,
            textAlign: TextAlign.center,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 18,
              fontWeight: FontWeight.w600,
              letterSpacing: 2,
              fontFamily: 'monospace',
            ),
          ),
        ),
        const SizedBox(height: 16),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(context.tr('card_holder'),
                    style: TextStyle(
                        color: Colors.white60,
                        fontSize: 10,
                        letterSpacing: 1)),
                const SizedBox(height: 2),
                Text(
                  (card.cardHolder ?? context.tr('card_holder_fallback')).toUpperCase(),
                  style: const TextStyle(
                      color: Colors.white,
                      fontSize: 13,
                      fontWeight: FontWeight.w600),
                ),
              ],
            ),
            if (card.expiryDate != null)
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(context.tr('expires'),
                      style: TextStyle(
                          color: Colors.white60,
                          fontSize: 10,
                          letterSpacing: 1)),
                  const SizedBox(height: 2),
                  Text(card.expiryDate!,
                      style: const TextStyle(
                          color: Colors.white,
                          fontSize: 13,
                          fontWeight: FontWeight.w600)),
                ],
              ),
          ],
        ),
      ],
    );
  }

  Widget _compactContent(BuildContext context) {
    return Row(
      children: [
        _networkLogo(),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              _cardTypeBadge(context),
              const SizedBox(height: 4),
              Text(
                card.maskedNumber,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  letterSpacing: 1.5,
                  fontFamily: 'monospace',
                ),
              ),
            ],
          ),
        ),
        if (card.balance != null)
          Text(
            '\$${card.balance!.toStringAsFixed(2)}',
            style: const TextStyle(
                color: Colors.white,
                fontSize: 16,
                fontWeight: FontWeight.bold),
          ),
      ],
    );
  }

  Widget _cardTypeBadge(BuildContext context) {
    final label = card.cardType == 'visa'
        ? context.tr('visa').toUpperCase()
        : card.cardType == 'master'
            ? context.tr('mastercard').toUpperCase()
            : (card.isAddon == true
                ? context.tr('virtual_addon').toUpperCase()
                : context.tr('virtual').toUpperCase());
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(label,
          style: const TextStyle(
              color: Colors.white,
              fontSize: 11,
              fontWeight: FontWeight.w700,
              letterSpacing: 0.5)),
    );
  }

  Widget _networkLogo() {
    if (card.cardType == 'visa') {
      return const Text('VISA',
          style: TextStyle(
              color: Colors.white,
              fontSize: 22,
              fontWeight: FontWeight.w900,
              fontStyle: FontStyle.italic));
    }
    // Mastercard circles
    return SizedBox(
      width: 40,
      height: 26,
      child: Stack(
        children: [
          Positioned(
            left: 0,
            child: Container(
              width: 26,
              height: 26,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.red.withValues(alpha: 0.8),
              ),
            ),
          ),
          Positioned(
            right: 0,
            child: Container(
              width: 26,
              height: 26,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.orange.withValues(alpha: 0.8),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// Pending card placeholder
class PendingCardWidget extends StatelessWidget {
  final String userEmail;
  final String cardType;

  const PendingCardWidget({
    super.key,
    required this.userEmail,
    required this.cardType,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppTheme.surfaceLight,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.warning.withValues(alpha: 0.5)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: AppTheme.warning.withValues(alpha: 0.15),
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.schedule, color: AppTheme.warning, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '${cardType.toUpperCase()} ${context.tr('card_pending')}',
                  style: const TextStyle(
                      color: AppTheme.textPrimary,
                      fontWeight: FontWeight.w600,
                      fontSize: 14),
                ),
                const SizedBox(height: 2),
                Text(userEmail,
                    style: const TextStyle(
                        color: AppTheme.textSecondary, fontSize: 12)),
              ],
            ),
          ),
          const Icon(Icons.chevron_right, color: AppTheme.textSecondary),
        ],
      ),
    );
  }
}
