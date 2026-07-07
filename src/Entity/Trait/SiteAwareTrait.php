<?php

namespace App\Entity\Trait;

use App\Entity\Site;
use Doctrine\ORM\Mapping as ORM;

/**
 * Reusable trait for any entity that belongs to a specific Site (photo or video).
 * The relation is mandatory at the DB level once data has been migrated.
 */
trait SiteAwareTrait
{
    #[ORM\ManyToOne(targetEntity: Site::class)]
    #[ORM\JoinColumn(name: 'site_id', referencedColumnName: 'id', nullable: true)]
    private ?Site $site = null;

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): static
    {
        $this->site = $site;

        return $this;
    }
}
