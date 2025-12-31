import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:file_picker/file_picker.dart';
import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../../../../core/config/app_config.dart';

class ProVerificationPage extends ConsumerStatefulWidget {
  const ProVerificationPage({super.key});

  @override
  ConsumerState<ProVerificationPage> createState() => _ProVerificationPageState();
}

class _ProVerificationPageState extends ConsumerState<ProVerificationPage> {
  final _siretController = TextEditingController();
  final _storage = const FlutterSecureStorage();
  bool _isLoading = false;
  String? _status;
  bool _hasIdDocument = false;

  @override
  void dispose() {
    _siretController.dispose();
    super.dispose();
  }

  Future<void> _loadProfile() async {
    setState(() => _isLoading = true);
    try {
      final token = await _storage.read(key: 'auth_token');
      final dio = Dio(
        BaseOptions(
          baseUrl: AppConfig.apiBaseUrl,
          headers: {
            'Authorization': 'Bearer $token',
            'Content-Type': 'application/json',
          },
        ),
      );

      final response = await dio.get('/api/profiles/me');
      final data = response.data;
      setState(() {
        _status = data['status'];
        _hasIdDocument = data['hasIdDocument'] ?? false;
        if (data['siret'] != null) {
          _siretController.text = data['siret'];
        }
      });
    } catch (e) {
      // Ignorer les erreurs
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  void initState() {
    super.initState();
    _loadProfile();
  }

  Future<void> _submitSiret() async {
    if (_siretController.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Veuillez entrer un SIRET')),
      );
      return;
    }

    setState(() => _isLoading = true);
    try {
      final token = await _storage.read(key: 'auth_token');
      final dio = Dio(
        BaseOptions(
          baseUrl: AppConfig.apiBaseUrl,
          headers: {
            'Authorization': 'Bearer $token',
            'Content-Type': 'application/json',
          },
        ),
      );

      await dio.post('/api/profiles/start', data: {
        'siret': _siretController.text,
      });

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('SIRET enregistré')),
        );
        _loadProfile();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Erreur: $e')),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _uploadIdDocument() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png'],
    );

    if (result == null || result.files.single.path == null) {
      return;
    }

    setState(() => _isLoading = true);
    try {
      final token = await _storage.read(key: 'auth_token');
      final dio = Dio(
        BaseOptions(
          baseUrl: AppConfig.apiBaseUrl,
          headers: {
            'Authorization': 'Bearer $token',
          },
      ));

      final filePath = result.files.single.path!;
      final fileName = result.files.single.name;

      final formData = FormData.fromMap({
        'idDocument': await MultipartFile.fromFile(filePath, filename: fileName),
      });

      await dio.post('/api/profiles/upload-id', data: formData);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Document uploadé avec succès')),
        );
        _loadProfile();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Erreur: $e')),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Devenir PRO'),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Vérification professionnelle',
                            style: Theme.of(context).textTheme.headlineSmall,
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Pour devenir prestataire PRO, vous devez fournir votre SIRET et une pièce d\'identité.',
                            style: Theme.of(context).textTheme.bodyMedium,
                          ),
                          if (_status != null) ...[
                            const SizedBox(height: 16),
                            Chip(
                              label: Text('Statut: $_status'),
                              backgroundColor: _status == 'VERIFIED'
                                  ? Colors.green[100]
                                  : _status == 'REJECTED'
                                      ? Colors.red[100]
                                      : Colors.orange[100],
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),
                  TextField(
                    controller: _siretController,
                    decoration: const InputDecoration(
                      labelText: 'SIRET *',
                      hintText: '12345678901234',
                    ),
                  ),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: _submitSiret,
                    child: const Text('Enregistrer le SIRET'),
                  ),
                  const SizedBox(height: 24),
                  const Divider(),
                  const SizedBox(height: 16),
                  Text(
                    'Pièce d\'identité',
                    style: Theme.of(context).textTheme.titleMedium,
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Format accepté: PDF, JPG, PNG',
                    style: Theme.of(context).textTheme.bodySmall,
                  ),
                  if (_hasIdDocument) ...[
                    const SizedBox(height: 8),
                    Chip(
                      label: const Text('Document uploadé'),
                      backgroundColor: Colors.green[100],
                    ),
                  ],
                  const SizedBox(height: 16),
                  OutlinedButton.icon(
                    onPressed: _uploadIdDocument,
                    icon: const Icon(Icons.upload_file),
                    label: const Text('Uploader la pièce d\'identité'),
                  ),
                ],
              ),
            ),
    );
  }
}

