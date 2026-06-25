<?php

namespace App\Repository;

use App\Entity\Site;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Site>
 */
class SiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Site::class);
    }

    public function findBySlug(string $slug): ?Site
    {
        return $this->findOneBy(['slug' => $slug, 'isActive' => true]);
    }

    public function findByDomain(string $host): ?Site
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.isActive = true')
            ->andWhere('s.domain = :host OR s.domainStaging = :host')
            ->setParameter('host', $host)
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findDefault(): ?Site
    {
        return $this->findOneBy(['isDefault' => true, 'isActive' => true])
            ?? $this->findOneBy(['isActive' => true], ['position' => 'ASC']);
    }

    /**
     * @return Site[]
     */
    public function findAllActive(): array
    {
        return $this->findBy(['isActive' => true], ['position' => 'ASC']);
    }
}
