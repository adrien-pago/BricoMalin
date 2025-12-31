import 'package:dio/dio.dart';

import '../models/category_model.dart';
import 'api_client.dart';

class CategoryApi {
  final Dio _dio;

  CategoryApi({Dio? dio}) : _dio = dio ?? ApiClient().dio;

  Future<List<CategoryModel>> getCategories() async {
    try {
      final response = await _dio.get('/api/categories');
      return (response.data as List)
          .map((json) => CategoryModel.fromJson(json))
          .toList();
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

