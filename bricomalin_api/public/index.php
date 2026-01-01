<?php

use App\Kernel;

// Fix pour Apache qui ne transmet pas toujours le header Authorization à PHP
// On récupère le header depuis différentes sources possibles et on le met dans $_SERVER
if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
    // Essayer depuis REDIRECT_HTTP_AUTHORIZATION (si mod_rewrite est utilisé)
    if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $_SERVER['HTTP_AUTHORIZATION'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    // Essayer depuis getallheaders() si disponible
    elseif (function_exists('getallheaders')) {
        $headers = getallheaders();
        if ($headers) {
            foreach ($headers as $name => $value) {
                if (strtolower($name) === 'authorization') {
                    $_SERVER['HTTP_AUTHORIZATION'] = $value;
                    break;
                }
            }
        }
    }
    // Essayer depuis apache_request_headers() si disponible
    elseif (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        if ($headers) {
            if (isset($headers['Authorization'])) {
                $_SERVER['HTTP_AUTHORIZATION'] = $headers['Authorization'];
            } elseif (isset($headers['authorization'])) {
                $_SERVER['HTTP_AUTHORIZATION'] = $headers['authorization'];
            }
        }
    }
}

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};

