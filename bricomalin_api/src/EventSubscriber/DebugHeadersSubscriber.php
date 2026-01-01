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
        
        // Si le header n'existe pas, on essaie de le récupérer depuis $_SERVER
        if (!$authorization) {
            // Apache peut stocker le header dans différentes variables $_SERVER
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $authorization = $_SERVER['HTTP_AUTHORIZATION'];
                $request->headers->set('Authorization', $authorization);
            } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $authorization = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
                $request->headers->set('Authorization', $authorization);
            } elseif (function_exists('apache_request_headers')) {
                // Dernière tentative avec apache_request_headers()
                $apacheHeaders = apache_request_headers();
                if (isset($apacheHeaders['Authorization'])) {
                    $authorization = $apacheHeaders['Authorization'];
                    $request->headers->set('Authorization', $authorization);
                } elseif (isset($apacheHeaders['authorization'])) {
                    // Certaines versions d'Apache mettent en minuscules
                    $authorization = $apacheHeaders['authorization'];
                    $request->headers->set('Authorization', $authorization);
                }
            }
        }

        // Log des headers pour déboguer
        error_log('=== DEBUG HEADERS ===');
        error_log('Path: ' . $request->getPathInfo());
        error_log('Authorization header: ' . ($authorization ?: 'NOT FOUND'));
        error_log('All headers: ' . json_encode($request->headers->all()));
        error_log('===================');
    }
}

