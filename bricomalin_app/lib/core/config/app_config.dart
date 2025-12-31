import 'package:flutter_dotenv/flutter_dotenv.dart';

class AppConfig {
  static String? _apiBaseUrl;

  static void initialize() {
    // Priorité: --dart-define > .env > valeur par défaut
    _apiBaseUrl = const String.fromEnvironment(
      'API_BASE_URL',
      defaultValue: '',
    );

    if (_apiBaseUrl!.isEmpty) {
      _apiBaseUrl = dotenv.env['API_BASE_URL'] ?? 'http://localhost:8000';
    }
  }

  static String get apiBaseUrl => _apiBaseUrl ?? 'http://localhost:8000';
}

