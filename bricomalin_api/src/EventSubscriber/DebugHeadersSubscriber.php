<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class DebugHeadersSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 1000],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        
        // Seulement pour les routes API
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        // Fix pour Apache qui ne transmet pas toujours le header Authorization
        // On vérifie d'abord si le header existe dans la Request
        $authorization = $request->headers->get('Authorization');
        
        // Log détaillé pour debug
        error_log('=== DEBUG HEADERS ===');
        error_log('Path: ' . $request->getPathInfo());
        error_log('Method: ' . $request->getMethod());
        error_log('Authorization from Request headers: ' . ($authorization ?: 'NOT FOUND'));
        
        // Si le header n'existe pas, on essaie de le récupérer depuis $_SERVER
        if (!$authorization) {
            error_log('Trying to get Authorization from $_SERVER...');
            
            // Log toutes les variables $_SERVER qui contiennent "AUTHORIZATION"
            $authRelated = [];
            foreach ($_SERVER as $key => $value) {
                if (stripos($key, 'AUTHORIZATION') !== false || stripos($key, 'AUTH') !== false) {
                    $authRelated[$key] = $value;
                }
            }
            error_log('$_SERVER auth-related keys: ' . json_encode(array_keys($authRelated)));
            
            // Apache peut stocker le header dans différentes variables $_SERVER
            // REDIRECT_HTTP_AUTHORIZATION est souvent utilisé par mod_rewrite
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) && !empty(trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']))) {
                $authorization = trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
                $request->headers->set('Authorization', $authorization);
                error_log('Found in $_SERVER[REDIRECT_HTTP_AUTHORIZATION]');
            } elseif (isset($_SERVER['HTTP_AUTHORIZATION']) && !empty(trim($_SERVER['HTTP_AUTHORIZATION']))) {
                $authorization = trim($_SERVER['HTTP_AUTHORIZATION']);
                $request->headers->set('Authorization', $authorization);
                error_log('Found in $_SERVER[HTTP_AUTHORIZATION]');
            } elseif (function_exists('apache_request_headers')) {
                error_log('Trying apache_request_headers()...');
                // Dernière tentative avec apache_request_headers()
                $apacheHeaders = apache_request_headers();
                error_log('apache_request_headers() returned: ' . json_encode(array_keys($apacheHeaders ?? [])));
                if (isset($apacheHeaders['Authorization'])) {
                    $authorization = $apacheHeaders['Authorization'];
                    $request->headers->set('Authorization', $authorization);
                    error_log('Found in apache_request_headers()[Authorization]');
                } elseif (isset($apacheHeaders['authorization'])) {
                    // Certaines versions d'Apache mettent en minuscules
                    $authorization = $apacheHeaders['authorization'];
                    $request->headers->set('Authorization', $authorization);
                    error_log('Found in apache_request_headers()[authorization]');
                }
            }
            
            // Si toujours pas trouvé, lire directement depuis getallheaders() si disponible
            if (!$authorization && function_exists('getallheaders')) {
                error_log('Trying getallheaders()...');
                $allHeaders = getallheaders();
                if ($allHeaders) {
                    error_log('getallheaders() returned: ' . json_encode(array_keys($allHeaders)));
                    foreach ($allHeaders as $key => $value) {
                        if (strtolower($key) === 'authorization') {
                            $authorization = $value;
                            $request->headers->set('Authorization', $authorization);
                            error_log('Found in getallheaders()[' . $key . ']');
                            break;
                        }
                    }
                }
            }
        }

        // Log final
        error_log('Final Authorization header: ' . ($authorization ?: 'NOT FOUND'));
        error_log('All Request headers: ' . json_encode($request->headers->all()));
        error_log('===================');
    }
}

