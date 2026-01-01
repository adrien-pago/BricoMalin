<?php

use App\Kernel;

// CRITIQUE : Fix pour Apache/FastCGI qui ne transmet pas le header Authorization à PHP
// Le header Authorization est souvent filtré par Apache avec FastCGI/PHP-FPM
// On essaie plusieurs méthodes pour le récupérer et le mettre dans $_SERVER['HTTP_AUTHORIZATION']

function getAuthorizationHeader(): ?string
{
    // Méthode 1: Depuis $_SERVER directement (si mod_rewrite a fonctionné)
    if (isset($_SERVER['HTTP_AUTHORIZATION']) && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
        return $_SERVER['HTTP_AUTHORIZATION'];
    }
    
    // Méthode 2: Depuis REDIRECT_HTTP_AUTHORIZATION (si mod_rewrite avec redirection)
    if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) && !empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    
    // Méthode 3: Depuis getallheaders() (FPM/CGI)
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        if ($headers) {
            foreach ($headers as $name => $value) {
                if (strtolower($name) === 'authorization' && !empty($value)) {
                    return $value;
                }
            }
        }
    }
    
    // Méthode 4: Depuis apache_request_headers() (Apache module)
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        if ($headers) {
            if (isset($headers['Authorization']) && !empty($headers['Authorization'])) {
                return $headers['Authorization'];
            }
            if (isset($headers['authorization']) && !empty($headers['authorization'])) {
                return $headers['authorization'];
            }
        }
    }
    
    // Méthode 5: Parser les headers bruts depuis $_SERVER
    // Certains serveurs stockent les headers dans des variables spécifiques
    foreach ($_SERVER as $key => $value) {
        if (stripos($key, 'AUTHORIZATION') !== false && !empty($value)) {
            return $value;
        }
    }
    
    return null;
}

// Récupérer et définir le header Authorization dans $_SERVER
$authHeader = getAuthorizationHeader();
if ($authHeader !== null) {
    if (!isset($_SERVER['HTTP_AUTHORIZATION']) || empty($_SERVER['HTTP_AUTHORIZATION'])) {
        $_SERVER['HTTP_AUTHORIZATION'] = $authHeader;
        error_log('[index.php] Authorization header found and set in $_SERVER[HTTP_AUTHORIZATION]: ' . substr($authHeader, 0, 20) . '...');
    }
} else {
    // Log pour debug si le header n'est pas trouvé
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        error_log('[index.php] WARNING: Authorization header NOT FOUND for API request: ' . $_SERVER['REQUEST_URI']);
        error_log('[index.php] Available $_SERVER keys: ' . implode(', ', array_filter(array_keys($_SERVER), function($k) {
            return stripos($k, 'HTTP') === 0 || stripos($k, 'AUTH') !== false || stripos($k, 'REDIRECT') !== false;
        })));
    }
}

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};


