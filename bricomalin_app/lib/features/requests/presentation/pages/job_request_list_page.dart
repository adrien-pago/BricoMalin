import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../data/api/job_request_api.dart';
import '../../../../data/models/job_request_model.dart';
import 'job_request_detail_page.dart';
import 'create_job_request_page.dart';

final jobRequestListProvider = FutureProvider<List<JobRequestModel>>((ref) async {
  final api = JobRequestApi();
  return api.getJobRequests();
});

class JobRequestListPage extends ConsumerWidget {
  const JobRequestListPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final jobRequestsAsync = ref.watch(jobRequestListProvider);

    return Scaffold(
      body: jobRequestsAsync.when(
        data: (requests) {
          if (requests.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.inbox,
                    size: 80,
                    color: Colors.grey[400],
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'Aucune demande',
                    style: Theme.of(context).textTheme.headlineSmall,
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Soyez le premier à créer une demande',
                    style: Theme.of(context).textTheme.bodyMedium,
                  ),
                ],
              ),
            );
          }

          return RefreshIndicator(
            onRefresh: () => ref.refresh(jobRequestListProvider.future),
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: requests.length,
              itemBuilder: (context, index) {
                final request = requests[index];
                return Card(
                  child: ListTile(
                    title: Text(request.title),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const SizedBox(height: 4),
                        Text('${request.department} ${request.city ?? ''}'),
                        Text(
                          request.categoryName,
                          style: Theme.of(context).textTheme.bodySmall,
                        ),
                      ],
                    ),
                    trailing: request.isFree
                        ? const Chip(
                            label: Text('Gratuit'),
                            backgroundColor: Colors.green,
                          )
                        : Text(
                            '${request.suggestedPrice}€',
                            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                  color: Theme.of(context).colorScheme.primary,
                                  fontWeight: FontWeight.bold,
                                ),
                          ),
                    onTap: () => context.push('/requests/${request.id}'),
                  ),
                );
              },
            ),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stack) => Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.error_outline, size: 64, color: Colors.red[300]),
              const SizedBox(height: 16),
              Text('Erreur: $error'),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => ref.refresh(jobRequestListProvider),
                child: const Text('Réessayer'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

