# Lancer l'application Flutter

## Configuration de l'URL API

L'application doit pointer vers votre VPS, pas vers localhost.

### Option 1 : Via --dart-define (recommandé)

```bash
flutter run --dart-define=API_BASE_URL=https://votre-domaine.com
```

Remplacez `https://votre-domaine.com` par l'URL réelle de votre API sur le VPS.

### Option 2 : Via fichier .env

Créez/modifiez le fichier `.env` dans `bricomalin_app/` :

```
API_BASE_URL=https://votre-domaine.com
```

Puis lancez normalement :

```bash
flutter run
```

## Lancer l'application

```bash
cd bricomalin_app
flutter run
```

Ou pour un appareil spécifique :

```bash
# Windows desktop
flutter run -d windows

# Chrome (web)
flutter run -d chrome

# Android (si connecté)
flutter run -d android

# iOS (si connecté)
flutter run -d ios
```

## Vérifier la connexion

Une fois l'app lancée, testez :
1. L'inscription d'un nouvel utilisateur
2. La connexion
3. La liste des demandes

Si vous avez des erreurs de connexion, vérifiez :
- Que l'URL de l'API est correcte
- Que votre VPS est accessible
- Que CORS est bien configuré sur le VPS

