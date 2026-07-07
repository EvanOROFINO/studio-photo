<?php

namespace App\Repository;

use App\Entity\Site;
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

    /**
     * @return Testimonial[]
     * When a $site is given, returns its testimonials (plus legacy ones
     * with no site assigned yet).
     */
    public function findPublishedOrdered(?int $limit = null, ?Site $site = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.published = :true')
            ->setParameter('true', true)
            ->orderBy('t.position', 'ASC')
            ->addOrderBy('t.createdAt', 'DESC');

        if ($site !== null) {
            $qb->andWhere('t.site = :site OR t.site IS NULL')
               ->setParameter('site', $site);
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}
