<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /** @return Product[] */
    public function findPublishedOrdered(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.published = :true')
            ->setParameter('true', true)
            ->orderBy('p.position', 'ASC')
            ->addOrderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPublishedBySlug(string $slug): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.slug = :slug')
            ->andWhere('p.published = :true')
            ->setParameter('slug', $slug)
            ->setParameter('true', true)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
