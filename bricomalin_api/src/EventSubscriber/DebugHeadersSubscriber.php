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

        // Log des headers pour déboguer
        $authorization = $request->headers->get('Authorization');
        error_log('=== DEBUG HEADERS ===');
        error_log('Path: ' . $request->getPathInfo());
        error_log('Authorization header: ' . ($authorization ?: 'NOT FOUND'));
        error_log('All headers: ' . json_encode($request->headers->all()));
        error_log('===================');
    }
}

