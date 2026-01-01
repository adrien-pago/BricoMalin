<?php

use App\Kernel;

// CRITIQUE : Fix pour Apache/FastCGI qui ne transmet pas le header Authorization à PHP
// Le header Authorization est souvent filtré par Apache avec FastCGI/PHP-FPM
// On essaie plusieurs méthodes pour le récupérer et le mettre dans $_SERVER['HTTP_AUTHORIZATION']

if (!function_exists('getAuthorizationHeader')) {
    function getAuthorizationHeader(): ?string
    {
        // Méthode 1: Depuis REDIRECT_HTTP_AUTHORIZATION (priorité - mod_rewrite avec redirection)
        // C'est souvent là que le header se trouve avec Apache + mod_rewrite
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) && !empty(trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']))) {
            return trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
        }
        
        // Méthode 2: Depuis $_SERVER directement (si mod_rewrite a fonctionné)
        if (isset($_SERVER['HTTP_AUTHORIZATION']) && !empty(trim($_SERVER['HTTP_AUTHORIZATION']))) {
            return trim($_SERVER['HTTP_AUTHORIZATION']);
        }
        
        // Méthode 3: Parser tous les headers depuis $_SERVER (chercher toutes les clés contenant AUTHORIZATION)
        foreach ($_SERVER as $key => $value) {
            if (stripos($key, 'AUTHORIZATION') !== false && !empty(trim($value))) {
                return trim($value);
            }
        }
        
        // Méthode 4: Depuis getallheaders() (FPM/CGI)
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if ($headers) {
                foreach ($headers as $name => $value) {
                    if (strtolower($name) === 'authorization' && !empty(trim($value))) {
                        return trim($value);
                    }
                }
            }
        }
        
        // Méthode 5: Depuis apache_request_headers() (Apache module)
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if ($headers) {
                if (isset($headers['Authorization']) && !empty(trim($headers['Authorization']))) {
                    return trim($headers['Authorization']);
                }
                if (isset($headers['authorization']) && !empty(trim($headers['authorization']))) {
                    return trim($headers['authorization']);
                }
            }
        }
        
        return null;
    }
}

// Récupérer et définir le header Authorization dans $_SERVER
// Note: Pour les requêtes OPTIONS (preflight CORS), le header Authorization n'est généralement pas envoyé
$isOptionsRequest = isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS';
$authHeader = getAuthorizationHeader();

if ($authHeader !== null) {
    if (!isset($_SERVER['HTTP_AUTHORIZATION']) || empty(trim($_SERVER['HTTP_AUTHORIZATION']))) {
        $_SERVER['HTTP_AUTHORIZATION'] = $authHeader;
        error_log('[index.php] Authorization header found and set in $_SERVER[HTTP_AUTHORIZATION]: ' . substr($authHeader, 0, 30) . '...');
    }
} elseif (!$isOptionsRequest) {
    // Log pour debug si le header n'est pas trouvé (sauf pour OPTIONS qui est normal)
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        $authKeys = array_filter(array_keys($_SERVER), function($k) {
            return stripos($k, 'HTTP') === 0 || stripos($k, 'AUTH') !== false || stripos($k, 'REDIRECT') !== false;
        });
        error_log('[index.php] WARNING: Authorization header NOT FOUND for: ' . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI']);
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            error_log('[index.php] REDIRECT_HTTP_AUTHORIZATION exists but is empty: "' . $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] . '"');
        }
    }
}

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};


