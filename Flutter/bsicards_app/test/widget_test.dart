import 'package:flutter_test/flutter_test.dart';
import 'package:bsicards_app/config/app_theme.dart';

void main() {

  test('Theme primary color is configured', () {
    expect(AppTheme.primary.value, equals(0xFF2AABEE));
  });
}
