import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../../core/config/app_config.dart';
import '../models/user_model.dart';
import 'api_client.dart';

class AuthApi {
  final Dio _dio;
  final FlutterSecureStorage _storage = const FlutterSecureStorage();

  AuthApi({Dio? dio}) : _dio = dio ?? Dio(
    BaseOptions(
      baseUrl: AppConfig.apiBaseUrl,
      headers: {'Content-Type': 'application/json'},
    ),
  ) {
    // Ajouter l'intercepteur pour le token
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await _storage.read(key: 'auth_token');
          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          return handler.next(options);
        },
      ),
    );
  }

  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final response = await _dio.post(
        '/api/auth/login',
        data: {'email': email, 'password': password},
      );
      return response.data;
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Future<void> register(String email, String password, String displayName) async {
    try {
      await _dio.post(
        '/api/auth/register',
        data: {
          'email': email,
          'password': password,
          'displayName': displayName,
        },
      );
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Future<UserModel> getMe() async {
    try {
      final response = await _dio.get('/api/me');
      return UserModel.fromJson(response.data);
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  String _handleError(DioException error) {
    if (error.response != null) {
      final data = error.response!.data;
      if (data is Map && data['error'] != null) {
        return data['error']['message'] ?? 'Une erreur est survenue';
      }
      return 'Erreur ${error.response!.statusCode}';
    }
    return 'Erreur de connexion';
  }
}

