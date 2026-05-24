<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Photo;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Photo>
 */
class PhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Photo::class);
    }

    /** @return Photo[] */
    public function findFeatured(int $limit = 8): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.featured = :true')
            ->setParameter('true', true)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** @return Photo[] */
    public function findByCategory(Category $category): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.category = :category')
            ->setParameter('category', $category)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return Paginator<Photo> */
    public function paginate(int $page = 1, int $perPage = 24): Paginator
    {
        $qb = $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        return new Paginator($qb->getQuery(), fetchJoinCollection: false);
    }

    /** @return Paginator<Photo> */
    public function paginateByTag(Tag $tag, int $page = 1, int $perPage = 24): Paginator
    {
        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.tags', 't')
            ->andWhere('t = :tag')
            ->setParameter('tag', $tag)
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        return new Paginator($qb->getQuery(), fetchJoinCollection: false);
    }

    public function findPrevious(Photo $photo): ?Photo
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.createdAt > :date')
            ->setParameter('date', $photo->getCreatedAt())
            ->orderBy('p.createdAt', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findNext(Photo $photo): ?Photo
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.createdAt < :date')
            ->setParameter('date', $photo->getCreatedAt())
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
