import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../features/auth/presentation/pages/login_page.dart';
import '../../features/auth/presentation/pages/register_page.dart';
import '../../features/home/presentation/pages/home_page.dart';
import '../../features/onboarding/presentation/pages/onboarding_page.dart';
import '../../features/splash/presentation/pages/splash_page.dart';
import '../../features/requests/presentation/pages/job_request_detail_page.dart';
import '../../features/requests/presentation/pages/job_request_list_page.dart';
import '../../features/requests/presentation/pages/create_job_request_page.dart';
import '../../features/profile/presentation/pages/profile_page.dart';
import '../../features/pro/presentation/pages/pro_verification_page.dart';
import '../../features/payment/presentation/pages/payment_page.dart';
import '../../features/payment/presentation/pages/confirm_after_work_page.dart';
import '../auth/auth_provider.dart';

final routerProvider = Provider<GoRouter>((ref) {
  final authState = ref.watch(authProvider);

  return GoRouter(
    initialLocation: '/splash',
    redirect: (context, state) {
      final isAuthenticated = authState.valueOrNull != null;
      final isOnAuthPage = state.matchedLocation == '/login' || 
                          state.matchedLocation == '/register' ||
                          state.matchedLocation == '/onboarding';

      if (!isAuthenticated && !isOnAuthPage && state.matchedLocation != '/splash') {
        return '/login';
      }

      if (isAuthenticated && (state.matchedLocation == '/login' || 
                              state.matchedLocation == '/register' ||
                              state.matchedLocation == '/onboarding')) {
        return '/home';
      }

      return null;
    },
    routes: [
      GoRoute(
        path: '/splash',
        builder: (context, state) => const SplashPage(),
      ),
      GoRoute(
        path: '/onboarding',
        builder: (context, state) => const OnboardingPage(),
      ),
      GoRoute(
        path: '/login',
        builder: (context, state) => const LoginPage(),
      ),
      GoRoute(
        path: '/register',
        builder: (context, state) => const RegisterPage(),
      ),
      GoRoute(
        path: '/home',
        builder: (context, state) => const HomePage(),
      ),
      GoRoute(
        path: '/requests',
        builder: (context, state) => const JobRequestListPage(),
      ),
      GoRoute(
        path: '/requests/create',
        builder: (context, state) => const CreateJobRequestPage(),
      ),
      GoRoute(
        path: '/requests/:id',
        builder: (context, state) {
          final id = int.parse(state.pathParameters['id']!);
          return JobRequestDetailPage(jobRequestId: id);
        },
      ),
      GoRoute(
        path: '/profile',
        builder: (context, state) => const ProfilePage(),
      ),
      GoRoute(
        path: '/pro',
        builder: (context, state) => const ProVerificationPage(),
      ),
      GoRoute(
        path: '/payment',
        builder: (context, state) {
          final paymentId = state.uri.queryParameters['paymentId'];
          return PaymentPage(paymentId: paymentId);
        },
      ),
      GoRoute(
        path: '/payment/confirm',
        builder: (context, state) {
          final paymentId = state.uri.queryParameters['paymentId'];
          return ConfirmAfterWorkPage(paymentId: paymentId ?? '');
        },
      ),
    ],
  );
});

