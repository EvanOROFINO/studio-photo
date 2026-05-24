<?php

namespace App\Repository;

use App\Entity\Video;
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
}
