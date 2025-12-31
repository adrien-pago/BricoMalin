import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../data/api/job_request_api.dart';
import '../../../../data/api/offer_api.dart';
import '../../../../data/models/job_request_model.dart';

final jobRequestDetailProvider = FutureProvider.family<JobRequestModel, int>((ref, id) async {
  final api = JobRequestApi();
  return api.getJobRequest(id);
});

class JobRequestDetailPage extends ConsumerWidget {
  final int jobRequestId;

  const JobRequestDetailPage({super.key, required this.jobRequestId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final jobRequestAsync = ref.watch(jobRequestDetailProvider(jobRequestId));

    return Scaffold(
      appBar: AppBar(
        title: const Text('Détail de la demande'),
      ),
      body: jobRequestAsync.when(
        data: (request) => SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              request.title,
                              style: Theme.of(context).textTheme.headlineSmall,
                            ),
                          ),
                          if (request.requester?.isPro == true)
                            Chip(
                              label: const Text('PRO'),
                              backgroundColor: Colors.blue[100],
                            ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Text(
                        request.categoryName,
                        style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                              color: Theme.of(context).colorScheme.primary,
                            ),
                      ),
                      const SizedBox(height: 16),
                      Text(
                        request.description,
                        style: Theme.of(context).textTheme.bodyLarge,
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Icon(Icons.location_on, size: 20),
                          const SizedBox(width: 4),
                          Text('${request.department} ${request.city ?? ''}'),
                        ],
                      ),
                      const SizedBox(height: 8),
                      if (request.isFree)
                        const Chip(
                          label: Text('Gratuit'),
                          backgroundColor: Colors.green,
                        )
                      else if (request.suggestedPrice != null)
                        Text(
                          'Prix indicatif: ${request.suggestedPrice}€',
                          style: Theme.of(context).textTheme.titleMedium,
                        ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
              ElevatedButton.icon(
                onPressed: () => _showOfferDialog(context, ref, request),
                icon: const Icon(Icons.send),
                label: const Text('Faire une offre'),
                style: ElevatedButton.styleFrom(
                  minimumSize: const Size(double.infinity, 50),
                ),
              ),
            ],
          ),
        ),
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
                onPressed: () => ref.refresh(jobRequestDetailProvider(jobRequestId)),
                child: const Text('Réessayer'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showOfferDialog(
    BuildContext context,
    WidgetRef ref,
    JobRequestModel request,
  ) {
    final amountController = TextEditingController();
    final messageController = TextEditingController();

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Faire une offre'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: amountController,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(
                labelText: 'Montant (€)',
                hintText: 'Laissez vide si gratuit',
              ),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: messageController,
              maxLines: 3,
              decoration: const InputDecoration(
                labelText: 'Message (optionnel)',
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Annuler'),
          ),
          ElevatedButton(
            onPressed: () async {
              try {
                final api = OfferApi();
                await api.createOffer(
                  jobRequestId: request.id,
                  amount: amountController.text.isEmpty
                      ? null
                      : amountController.text,
                  message: messageController.text.isEmpty
                      ? null
                      : messageController.text,
                );
                if (context.mounted) {
                  Navigator.pop(context);
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Offre envoyée avec succès')),
                  );
                  ref.refresh(jobRequestDetailProvider(jobRequestId));
                }
              } catch (e) {
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('Erreur: $e')),
                  );
                }
              }
            },
            child: const Text('Envoyer'),
          ),
        ],
      ),
    );
  }
}

