<?php

namespace App\Repository;

use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Service>
 */
class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }

    /** @return Service[] */
    public function findActiveOrdered(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.active = :true')
            ->setParameter('true', true)
            ->orderBy('s.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
