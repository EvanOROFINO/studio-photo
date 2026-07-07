<?php

namespace App\Service;

use App\Entity\Site;
use App\Repository\SiteRepository;

/**
 * Holds the current Site for the request lifetime.
 * Injectable into controllers, services and Twig globals.
 */
class SiteContext
{
    private ?Site $current = null;

    public function __construct(private readonly SiteRepository $siteRepository)
    {
    }

    public function setCurrent(Site $site): void
    {
        $this->current = $site;
    }

    public function getCurrent(): Site
    {
        if ($this->current === null) {
            $this->current = $this->siteRepository->findDefault()
                ?? throw new \LogicException('No active site is configured. Run "php bin/console app:install:sites".');
        }

        return $this->current;
    }

    public function isPhoto(): bool
    {
        return $this->getCurrent()->isPhotoSite();
    }

    public function isVideo(): bool
    {
        return $this->getCurrent()->isVideoSite();
    }
}
