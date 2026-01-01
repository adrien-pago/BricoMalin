import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/auth/auth_provider.dart';

class SplashPage extends ConsumerStatefulWidget {
  const SplashPage({super.key});

  @override
  ConsumerState<SplashPage> createState() => _SplashPageState();
}

class _SplashPageState extends ConsumerState<SplashPage> {
  @override
  void initState() {
    super.initState();
    _navigateToNext();
  }

  Future<void> _navigateToNext() async {
    // Attendre que l'état d'authentification soit chargé
    // On attend jusqu'à ce que l'état ne soit plus en loading
    int attempts = 0;
    while (attempts < 20 && mounted) {
      await Future.delayed(const Duration(milliseconds: 100));
      if (!mounted) return;
      
      final authState = ref.read(authProvider);
      if (!authState.isLoading) {
        break;
      }
      attempts++;
    }
    
    if (!mounted) return;
    
    final authState = ref.read(authProvider);
    final isAuthenticated = authState.valueOrNull != null;
    
    if (!mounted) return;
    
    if (isAuthenticated) {
      context.go('/home');
    } else {
      context.go('/onboarding');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).colorScheme.primary,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.build,
              size: 100,
              color: Colors.white,
            ),
            const SizedBox(height: 24),
            Text(
              'BricoMalin',
              style: Theme.of(context).textTheme.headlineLarge?.copyWith(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                  ),
            ),
            const SizedBox(height: 8),
            Text(
              'Votre partenaire bricolage',
              style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                    color: Colors.white70,
                  ),
            ),
          ],
        ),
      ),
    );
  }
}

