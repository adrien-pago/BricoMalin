import 'package:dio/dio.dart';

import '../../core/config/app_config.dart';
import '../models/job_request_model.dart';
import 'api_client.dart';

class JobRequestApi {
  final Dio _dio;

  JobRequestApi({Dio? dio}) : _dio = dio ?? ApiClient().dio;

  Future<List<JobRequestModel>> getJobRequests({
    String? department,
    int? categoryId,
    String? query,
  }) async {
    try {
      final queryParams = <String, dynamic>{};
      if (department != null) queryParams['department'] = department;
      if (categoryId != null) queryParams['category'] = categoryId;
      if (query != null) queryParams['q'] = query;

      final response = await _dio.get(
        '/api/job-requests',
        queryParameters: queryParams.isEmpty ? null : queryParams,
      );

      return (response.data as List)
          .map((json) => JobRequestModel.fromJson(json))
          .toList();
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Future<JobRequestModel> getJobRequest(int id) async {
    try {
      final response = await _dio.get('/api/job-requests/$id');
      return JobRequestModel.fromJson(response.data);
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Future<JobRequestModel> createJobRequest({
    required int categoryId,
    required String title,
    required String description,
    required String department,
    String? city,
    bool isFree = false,
    String? suggestedPrice,
  }) async {
    try {
      final response = await _dio.post(
        '/api/job-requests',
        data: {
          'categoryId': categoryId,
          'title': title,
          'description': description,
          'department': department,
          'city': city,
          'isFree': isFree,
          'suggestedPrice': suggestedPrice,
        },
      );
      return JobRequestModel.fromJson(response.data);
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

