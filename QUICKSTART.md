# Guide de démarrage rapide - BricoMalin

## Prérequis

- PHP 8.1+ avec Composer
- MySQL 8.0+ (ou Docker)
- Flutter 3.0+
- Node.js (optionnel pour Symfony)

## 1. Backend (API Symfony)

### Installation

```bash
cd bricomalin_api
composer install
```

### Configuration

1. Copier `.env` vers `.env.local` :
```bash
cp .env .env.local
```

2. Éditer `.env.local` avec vos paramètres :
```env
DATABASE_URL="mysql://root:password@127.0.0.1:3306/bricomalin?serverVersion=8.0.0&charset=utf8mb4"
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=votre_passphrase_ici
STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLIC_KEY=pk_test_...
```

3. Générer les clés JWT :
```bash
php bin/console lexik:jwt:generate-keypair
```

### Base de données

**Option A : MySQL local**
```bash
# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Charger les fixtures
php bin/console doctrine:fixtures:load
```

**Option B : Docker**
```bash
# Démarrer MySQL
docker-compose -f docker-compose.dev.yml up -d

# Attendre que MySQL soit prêt, puis :
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

### Démarrer le serveur

```bash
symfony server:start
# ou
php -S localhost:8000 -t public
```

L'API sera accessible sur `http://localhost:8000`

### Comptes de test (fixtures)

- **Utilisateur normal** : `user@example.com` / `password123`
- **Utilisateur PRO** : `pro@example.com` / `password123`

## 2. Frontend (App Flutter)

### Installation

```bash
cd bricomalin_app
flutter pub get
```

### Configuration

L'URL de l'API peut être définie de deux façons :

**Option A : --dart-define (recommandé)**
```bash
flutter run --dart-define=API_BASE_URL=http://localhost:8000
```

**Option B : Fichier .env**
Créer un fichier `.env` à la racine de `bricomalin_app/` :
```
API_BASE_URL=http://localhost:8000
```

### Lancer l'application

```bash
# Android
flutter run

# iOS (sur Mac)
flutter run -d ios

# Avec URL API personnalisée
flutter run --dart-define=API_BASE_URL=http://votre-api.com
```

## 3. Tests

### Tests API

```bash
cd bricomalin_api
php bin/phpunit
```

## 4. Documentation API

Une fois l'API démarrée, la documentation OpenAPI est disponible :
- Swagger UI : `http://localhost:8000/api/doc`
- OpenAPI YAML : `http://localhost:8000/openapi.yaml`

## 5. Structure des projets

### Backend (`bricomalin_api/`)
- `src/Entity/` - Entités Doctrine
- `src/Controller/` - Contrôleurs API REST
- `src/Service/` - Services métier (Stripe, Upload, etc.)
- `src/Repository/` - Repositories Doctrine
- `migrations/` - Migrations de base de données
- `src/DataFixtures/` - Données de test

### Frontend (`bricomalin_app/`)
- `lib/core/` - Configuration, thème, routes
- `lib/data/` - API clients, modèles
- `lib/domain/` - Modèles métier
- `lib/features/` - Écrans par fonctionnalité
- `lib/l10n/` - Traductions (FR, EN, ES)

## Notes importantes

1. **Stripe** : Utilisez des clés de test (`sk_test_...` et `pk_test_...`)
2. **JWT** : Les clés sont générées dans `config/jwt/` (à ne pas commiter)
3. **Uploads** : Les fichiers sont stockés dans `public/uploads/`
4. **CORS** : Configuré pour accepter les requêtes depuis l'app mobile

## Prochaines étapes

1. Configurer les clés Stripe dans `.env.local`
2. Tester l'inscription/connexion
3. Créer une demande de bricolage
4. Tester le système d'offres
5. Tester la vérification PRO

