# Installation sur VPS - BricoMalin API

## 1. Créer la base de données

Connectez-vous à MySQL sur votre VPS :

```bash
mysql -u user_brico_malin -p
```

Puis exécutez :

```sql
USE brico_malin;
```

## 2. Créer les tables

Exécutez le fichier SQL :

```bash
mysql -u user_brico_malin -p brico_malin < database_schema.sql
```

Ou copiez-collez le contenu de `database_schema.sql` dans votre client MySQL.

## 3. Insérer les données de test (optionnel)

```bash
mysql -u user_brico_malin -p brico_malin < database_fixtures.sql
```

**IMPORTANT** : Les mots de passe dans `database_fixtures.sql` sont des exemples. 
Pour générer les vrais hash, exécutez sur votre VPS :

```bash
php -r "echo password_hash('password123', PASSWORD_DEFAULT) . PHP_EOL;"
```

Puis remplacez les hash dans la base de données.

## 4. Générer les clés JWT

Sur votre VPS, dans le dossier du projet :

```bash
php bin/console lexik:jwt:generate-keypair
```

## 5. Configurer .env.local

Assurez-vous que votre `.env.local` contient :

```env
DATABASE_URL="mysql://user_brico_malin:Q&Unx*33urjXlqr0@127.0.0.1:3306/brico_malin?serverVersion=8.0.0&charset=utf8mb4"
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=votre_passphrase_ici
STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLIC_KEY=pk_test_...
```

## 6. Vider le cache

```bash
php bin/console cache:clear --env=prod
```

## 7. Vérifier que tout fonctionne

```bash
php bin/console doctrine:schema:validate
```

