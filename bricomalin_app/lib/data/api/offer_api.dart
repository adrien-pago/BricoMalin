import 'package:dio/dio.dart';

import 'api_client.dart';

class OfferApi {
  final Dio _dio;

  OfferApi({Dio? dio}) : _dio = dio ?? ApiClient().dio;

  Future<void> createOffer({
    required int jobRequestId,
    String? amount,
    String? message,
  }) async {
    try {
      await _dio.post(
        '/api/job-requests/$jobRequestId/offers',
        data: {
          'amount': amount,
          'message': message,
        },
      );
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

