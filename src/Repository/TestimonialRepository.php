<?php

namespace App\Repository;

use App\Entity\Testimonial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Testimonial>
 */
class TestimonialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Testimonial::class);
    }

    /** @return Testimonial[] */
    public function findPublishedOrdered(?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.published = :true')
            ->setParameter('true', true)
            ->orderBy('t.position', 'ASC')
            ->addOrderBy('t.createdAt', 'DESC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}
