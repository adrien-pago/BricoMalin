# BricoMalin App - Flutter

Application mobile Flutter pour BricoMalin.

## Installation

### 1. Installer les dépendances

```bash
flutter pub get
```

### 2. Configuration

L'URL de l'API peut être définie via :

**Option A : --dart-define**
```bash
flutter run --dart-define=API_BASE_URL=http://localhost:8000
```

**Option B : Fichier .env**
Créer un fichier `.env` à la racine :
```
API_BASE_URL=http://localhost:8000
```

### 3. Lancer l'application

```bash
# Android
flutter run

# iOS (sur Mac)
flutter run -d ios

# Avec URL API personnalisée
flutter run --dart-define=API_BASE_URL=http://votre-api.com
```

## Structure

- `lib/core/` - Configuration, thème, routes
- `lib/data/` - API clients, repositories
- `lib/domain/` - Modèles, interfaces
- `lib/features/` - Écrans et logique métier par fonctionnalité
- `lib/l10n/` - Fichiers de traduction (ARB)

## Fonctionnalités

- ✅ Authentification (inscription/connexion)
- ✅ Liste et création de demandes
- ✅ Système d'offres
- ✅ Profil utilisateur
- ✅ Vérification PRO
- ✅ Paiement Stripe (avant/après)
- ✅ i18n (FR, EN, ES)

## Build

### Android
```bash
flutter build apk --release
```

### iOS
```bash
flutter build ios --release
```

