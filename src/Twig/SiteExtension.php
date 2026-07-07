<?php

namespace App\Twig;

use App\Repository\SiteRepository;
use App\Service\SiteContext;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * Exposes {{ current_site }} and {{ all_sites }} as global Twig variables.
 * Templates can do : {% if current_site.videoSite %} … {% endif %}
 * and loop over all_sites to build a site switcher.
 */
class SiteExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly SiteContext $siteContext,
        private readonly SiteRepository $siteRepository,
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'current_site' => $this->siteContext->getCurrent(),
            'all_sites' => $this->siteRepository->findAllActive(),
        ];
    }
}
