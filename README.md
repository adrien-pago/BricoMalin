# BricoMalin

Plateforme de services de bricolage en France - Application mobile Flutter + API Symfony

## Structure du projet

```
BricoMalin/
├── bricomalin_api/     # API Symfony (Backend)
├── bricomalin_app/     # Application Flutter (Frontend)
└── README.md           # Ce fichier
```

## Démarrage rapide

### Prérequis

- PHP 8.1+ avec Composer
- MySQL 8.0+
- Flutter 3.0+
- Node.js (pour les assets Symfony, optionnel)

### Backend (API Symfony)

```bash
cd bricomalin_api
composer install
cp .env .env.local
# Éditer .env.local avec vos paramètres (DATABASE_URL, JWT_SECRET_KEY, STRIPE_SECRET_KEY)
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
php bin/console lexik:jwt:generate-keypair
symfony server:start
```

L'API sera accessible sur `http://localhost:8000`

### Frontend (App Flutter)

```bash
cd bricomalin_app
flutter pub get
flutter run --dart-define=API_BASE_URL=http://localhost:8000
```

## Documentation

- [Architecture Backend](bricomalin_api/ARCHITECTURE.md)
- [Architecture Frontend](bricomalin_app/ARCHITECTURE.md)
- [API Documentation](bricomalin_api/public/openapi.yaml)

## Fonctionnalités MVP

- ✅ Authentification JWT
- ✅ Gestion des demandes de bricolage
- ✅ Système d'offres
- ✅ Vérification PRO (SIRET + pièce d'identité)
- ✅ Paiement Stripe (avant ou après validation par codes)
- ✅ i18n (FR complet, EN/ES placeholders)

## Déploiement VPS

Voir les README respectifs dans chaque dossier pour les instructions de déploiement.

