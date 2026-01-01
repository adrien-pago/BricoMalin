import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../../data/api/auth_api.dart';
import '../../data/models/user_model.dart';

final storageProvider = Provider<FlutterSecureStorage>((ref) {
  return const FlutterSecureStorage();
});

final authApiProvider = Provider<AuthApi>((ref) {
  return AuthApi();
});

final authProvider = StateNotifierProvider<AuthNotifier, AsyncValue<UserModel?>>((ref) {
  return AuthNotifier(
    ref.read(authApiProvider),
    ref.read(storageProvider),
  );
});

class AuthNotifier extends StateNotifier<AsyncValue<UserModel?>> {
  final AuthApi _authApi;
  final FlutterSecureStorage _storage;

  AuthNotifier(this._authApi, this._storage) : super(const AsyncValue.loading()) {
    _loadUser();
  }

  Future<void> _loadUser() async {
    try {
      final token = await _storage.read(key: 'auth_token');
      if (token != null) {
        final user = await _authApi.getMe();
        state = AsyncValue.data(user);
      } else {
        state = const AsyncValue.data(null);
      }
    } catch (e) {
      state = AsyncValue.error(e, StackTrace.current);
    }
  }

  Future<void> login(String email, String password) async {
    state = const AsyncValue.loading();
    try {
      final response = await _authApi.login(email, password);
      
      // Debug: vérifier le format de la réponse
      print('Login response: $response');
      
      final token = response['token'];
      if (token == null) {
        throw Exception('Token non reçu de l\'API');
      }
      
      await _storage.write(key: 'auth_token', value: token);
      
      // Vérifier que le token est bien stocké
      final storedToken = await _storage.read(key: 'auth_token');
      print('Token stocké: ${storedToken != null ? 'Oui' : 'Non'}');
      
      // Attendre un peu pour que le storage soit bien synchronisé
      await Future.delayed(const Duration(milliseconds: 200));
      
      // Utiliser la même instance mais s'assurer que le token est bien lu
      final user = await _authApi.getMe();
      print('User récupéré: ${user.email}');
      
      state = AsyncValue.data(user);
    } catch (e) {
      print('Erreur login: $e');
      state = AsyncValue.error(e, StackTrace.current);
      rethrow;
    }
  }

  Future<void> register(String email, String password, String displayName) async {
    state = const AsyncValue.loading();
    try {
      await _authApi.register(email, password, displayName);
      await login(email, password);
    } catch (e) {
      state = AsyncValue.error(e, StackTrace.current);
      rethrow;
    }
  }

  Future<void> logout() async {
    await _storage.delete(key: 'auth_token');
    state = const AsyncValue.data(null);
  }
}

