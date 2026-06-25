<?php

namespace App\Repository;

use App\Entity\Site;
use App\Entity\Video;
use App\Entity\VideoCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Video>
 */
class VideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Video::class);
    }

    /** @return Video[] */
    public function findPublishedOrdered(): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.published = :true')
            ->setParameter('true', true)
            ->orderBy('v.position', 'ASC')
            ->addOrderBy('v.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return Video[] */
    public function findFeaturedForSite(Site $site, int $limit = 6): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.site = :site')
            ->andWhere('v.published = :true')
            ->andWhere('v.featured = :true')
            ->setParameter('site', $site)
            ->setParameter('true', true)
            ->orderBy('v.position', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** @return Video[] */
    public function findForSite(Site $site, ?VideoCategory $category = null): array
    {
        $qb = $this->createQueryBuilder('v')
            ->andWhere('v.site = :site')
            ->andWhere('v.published = :true')
            ->setParameter('site', $site)
            ->setParameter('true', true)
            ->orderBy('v.position', 'ASC')
            ->addOrderBy('v.createdAt', 'DESC');

        if ($category !== null) {
            $qb->andWhere('v.category = :category')
               ->setParameter('category', $category);
        }

        return $qb->getQuery()->getResult();
    }
}
