<?php

namespace App\Repository;

use App\Entity\BeforeAfter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BeforeAfter>
 */
class BeforeAfterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BeforeAfter::class);
    }

    /** @return BeforeAfter[] */
    public function findPublishedOrdered(): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.published = :true')
            ->setParameter('true', true)
            ->orderBy('b.position', 'ASC')
            ->addOrderBy('b.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
