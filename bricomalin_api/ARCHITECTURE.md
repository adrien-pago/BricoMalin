# Architecture Backend - BricoMalin API

## Structure

```
bricomalin_api/
├── config/              # Configuration Symfony
├── public/              # Point d'entrée web
├── src/
│   ├── Controller/      # Contrôleurs API REST
│   ├── Entity/          # Entités Doctrine
│   ├── Repository/      # Repositories Doctrine
│   ├── Service/         # Services métier
│   ├── Security/        # Configuration sécurité JWT
│   ├── EventSubscriber/ # Subscribers (ex: normalisation erreurs)
│   └── DataFixtures/    # Données de test
├── migrations/          # Migrations Doctrine
└── tests/              # Tests PHPUnit
```

## Principes

- **API REST JSON** : Tous les endpoints retournent du JSON
- **JWT Authentication** : Via LexikJWTAuthenticationBundle
- **Validation** : Symfony Validator sur toutes les entrées
- **Gestion d'erreurs** : Normalisée via EventSubscriber
- **Upload fichiers** : Stockage local dans `/public/uploads`
- **Stripe** : Intégration pour paiements (mode test)

## Flux principaux

### Authentification
1. Register/Login → JWT token
2. Token dans header `Authorization: Bearer {token}`
3. Endpoint `/api/me` pour profil utilisateur

### Création demande
1. POST `/api/job-requests` avec catégorie, description, département
2. Statut initial : `OPEN`
3. Visible par tous les utilisateurs authentifiés

### Système d'offres
1. POST `/api/job-requests/{id}/offers` pour proposer
2. Seul le créateur peut accepter
3. Une offre acceptée → statut demande = `ASSIGNED`

### Paiement
- **Mode BEFORE** : PaymentIntent créé immédiatement
- **Mode AFTER** : PaymentIntent avec `capture_method=manual`, capture après validation des 2 codes

### Vérification PRO
1. POST `/api/profiles/start` avec SIRET
2. POST `/api/profiles/upload-id` avec fichier
3. Statut : `PENDING` → `VERIFIED` (manuel pour MVP)

