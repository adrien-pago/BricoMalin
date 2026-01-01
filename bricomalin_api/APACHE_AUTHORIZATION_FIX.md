# Configuration Apache pour le header Authorization

## Problème

Apache avec FastCGI/PHP-FPM ne transmet pas automatiquement le header `Authorization` à PHP. Ce header est nécessaire pour l'authentification JWT.

## Solutions

### Solution 1 : Configuration dans le VirtualHost (Recommandé)

Si vous avez accès à la configuration Apache du VirtualHost, ajoutez ceci dans la section `<VirtualHost>` :

```apache
<VirtualHost *:443>
    # ... autres configurations ...
    
    # Forcer la transmission du header Authorization à PHP
    SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
    
    # Alternative avec mod_rewrite (si mod_rewrite est activé)
    RewriteEngine On
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    
    # ... autres configurations ...
</VirtualHost>
```

Puis redémarrez Apache :
```bash
sudo systemctl restart apache2
# ou
sudo systemctl restart httpd
```

### Solution 2 : Configuration dans Plesk

Si vous utilisez Plesk :

1. Allez dans **Domains** > votre domaine > **Apache & nginx Settings**
2. Dans **Additional directives for Apache**, ajoutez :
```apache
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```
3. Cliquez sur **OK** pour sauvegarder

### Solution 3 : Vérification de la configuration

Pour vérifier si le header arrive maintenant, consultez les logs PHP :

```bash
tail -f /var/log/apache2/error.log | grep "Authorization"
```

Vous devriez voir des messages indiquant que le header a été trouvé.

### Solution 4 : Vérifier les modules Apache

Assurez-vous que les modules nécessaires sont activés :

```bash
# Pour Debian/Ubuntu
sudo a2enmod rewrite headers env
sudo systemctl restart apache2

# Pour CentOS/RHEL
# Vérifiez que mod_rewrite, mod_headers et mod_env sont chargés dans httpd.conf
```

## Débogage

Si le problème persiste :

1. Vérifiez que le header est bien envoyé par le client (visible dans les logs Flutter)
2. Vérifiez les logs Apache pour voir si le header arrive au serveur
3. Vérifiez les logs PHP/error_log pour voir ce que le code PHP reçoit
4. Testez avec curl :
```bash
curl -H "Authorization: Bearer test_token" https://votre-domaine.com/api/me
```

## Note importante

Le fichier `.htaccess` dans `/public/.htaccess` contient déjà des règles pour gérer ce problème, mais certaines configurations Apache peuvent ignorer les fichiers `.htaccess` ou nécessiter une configuration au niveau du VirtualHost.

