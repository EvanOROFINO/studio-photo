<?php

namespace App\EventListener;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * En-têtes de sécurité de base ajoutés à toutes les réponses (sans écraser ceux déjà posés).
 * Pas de Content-Security-Policy : le site charge des polices et bibliothèques depuis des CDN
 * et intègre des vidéos YouTube, une politique stricte demande un réglage dédié à l'hébergement.
 */
#[AsEventListener(event: KernelEvents::RESPONSE)]
class SecurityHeadersListener
{
    public function __construct(
        #[Autowire('%kernel.environment%')] private readonly string $environment,
    ) {
    }

    public function __invoke(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $headers = $event->getResponse()->headers;
        $defaults = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
        ];

        // HSTS : uniquement en production et sur une connexion déjà sécurisée
        if ('prod' === $this->environment && $event->getRequest()->isSecure()) {
            $defaults['Strict-Transport-Security'] = 'max-age=15552000';
        }

        foreach ($defaults as $name => $value) {
            if (!$headers->has($name)) {
                $headers->set($name, $value);
            }
        }

        if (!headers_sent()) {
            header_remove('X-Powered-By');
        }
    }
}
