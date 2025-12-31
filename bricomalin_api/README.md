# BricoMalin API - Symfony

API REST JSON sécurisée avec JWT pour l'application BricoMalin.

## Installation

### 1. Installer les dépendances

```bash
composer install
```

### 2. Configuration

Copier `.env` vers `.env.local` et configurer :

```env
DATABASE_URL="mysql://user:password@127.0.0.1:3306/bricomalin?serverVersion=8.0.0&charset=utf8mb4"
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_passphrase_here
STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLIC_KEY=pk_test_...
```

### 3. Générer les clés JWT

```bash
php bin/console lexik:jwt:generate-keypair
```

### 4. Base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

### 5. Démarrer le serveur

```bash
symfony server:start
# ou
php -S localhost:8000 -t public
```

## Endpoints API

### Authentification
- `POST /api/auth/register` - Inscription
- `POST /api/auth/login` - Connexion
- `GET /api/me` - Profil utilisateur

### Catalogues
- `GET /api/categories` - Liste des catégories

### Demandes
- `GET /api/job-requests` - Liste des demandes (filtres: department, category, q, status)
- `POST /api/job-requests` - Créer une demande
- `GET /api/job-requests/{id}` - Détail d'une demande
- `PATCH /api/job-requests/{id}` - Modifier/Annuler une demande

### Offres
- `POST /api/job-requests/{id}/offers` - Faire une offre
- `GET /api/me/offers` - Mes offres
- `POST /api/offers/{id}/accept` - Accepter une offre
- `POST /api/offers/{id}/reject` - Rejeter une offre

### Profil PRO
- `POST /api/profiles/start` - Démarrer la vérification PRO (SIRET)
- `POST /api/profiles/upload-id` - Upload pièce d'identité
- `GET /api/profiles/me` - Mon profil PRO

### Paiements
- `POST /api/payments/create-intent` - Créer un PaymentIntent
- `POST /api/payments/confirm-after-work` - Valider après travaux (code)
- `GET /api/me/payments` - Mes paiements

## Tests

```bash
php bin/phpunit
```

## Déploiement VPS

1. Configurer `.env.prod` avec les variables de production
2. Exécuter les migrations : `php bin/console doctrine:migrations:migrate --env=prod`
3. Vider le cache : `php bin/console cache:clear --env=prod`
4. Configurer le serveur web (Nginx/Apache) pointant vers `public/`

