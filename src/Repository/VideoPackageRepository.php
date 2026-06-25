<?php

namespace App\Repository;

use App\Entity\Site;
use App\Entity\VideoPackage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VideoPackage>
 */
class VideoPackageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VideoPackage::class);
    }

    /** @return VideoPackage[] */
    public function findActiveForSite(Site $site): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.site = :site')
            ->andWhere('p.isActive = :true')
            ->setParameter('site', $site)
            ->setParameter('true', true)
            ->orderBy('p.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
