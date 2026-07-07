<?php

namespace App\EventListener;

use App\Repository\SiteRepository;
use App\Service\SiteContext;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * On every main request, detect the host and load the matching Site into SiteContext.
 * Falls back to the default Site if no host matches.
 */
#[AsEventListener(event: KernelEvents::REQUEST, priority: 100)]
class SiteListener
{
    public function __construct(
        private readonly SiteRepository $siteRepository,
        private readonly SiteContext $siteContext,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Allow URL override for dev/testing : ?_site=photo or ?_site=video
        $override = $request->query->get('_site');
        if ($override !== null) {
            $site = $this->siteRepository->findBySlug($override);
            if ($site !== null) {
                $this->siteContext->setCurrent($site);
                $request->getSession()?->set('_site_override', $override);

                return;
            }
        }

        // Persist override across requests within the session (dev mode)
        if ($request->hasSession() && ($persistedOverride = $request->getSession()->get('_site_override'))) {
            $site = $this->siteRepository->findBySlug($persistedOverride);
            if ($site !== null) {
                $this->siteContext->setCurrent($site);

                return;
            }
        }

        // Detect by host (including port for staging environments)
        $host = $request->getHttpHost();
        $site = $this->siteRepository->findByDomain($host);

        if ($site === null) {
            // Fallback : default site (Photo)
            $site = $this->siteRepository->findDefault();
        }

        if ($site !== null) {
            $this->siteContext->setCurrent($site);
        }
    }
}
