# Architecture Frontend - BricoMalin App

## Structure

```
lib/
├── core/
│   ├── config/          # Configuration (API URL, etc.)
│   ├── theme/           # Thème Material 3
│   ├── router/          # Configuration go_router
│   └── constants/       # Constantes
├── data/
│   ├── api/             # Clients API (Dio)
│   ├── models/          # DTOs
│   └── repositories/    # Implémentations repositories
├── domain/
│   ├── models/          # Modèles métier
│   └── repositories/    # Interfaces repositories
├── features/
│   ├── auth/            # Écrans auth
│   ├── home/            # Home avec onglets
│   ├── requests/        # Demandes (liste, détail, création)
│   ├── offers/          # Offres
│   ├── profile/         # Profil utilisateur
│   ├── pro/             # Vérification PRO
│   └── payment/         # Paiements
└── l10n/                # Traductions ARB
```

## Principes

- **State Management** : Riverpod
- **Navigation** : go_router
- **HTTP** : Dio avec intercepteurs
- **i18n** : flutter_localizations + intl
- **Architecture** : Clean-ish (séparation data/domain/features)

## Flux principaux

### Authentification
1. Splash → Onboarding → Register/Login
2. Token stocké (secure storage)
3. Intercepteur Dio ajoute token automatiquement

### Navigation
- Routes protégées nécessitent authentification
- Redirection automatique si non authentifié

### État
- Riverpod providers pour state management
- AsyncNotifier pour données asynchrones
- Écrans avec états : loading, error, empty, success

