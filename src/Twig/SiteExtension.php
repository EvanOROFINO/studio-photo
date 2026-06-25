<?php

namespace App\Twig;

use App\Service\SiteContext;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * Exposes {{ current_site }} as a global Twig variable.
 * Templates can do : {% if current_site.video %} … {% endif %}
 */
class SiteExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(private readonly SiteContext $siteContext)
    {
    }

    public function getGlobals(): array
    {
        return [
            'current_site' => $this->siteContext->getCurrent(),
        ];
    }
}
