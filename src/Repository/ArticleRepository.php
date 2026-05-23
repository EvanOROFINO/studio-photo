<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\ArticleCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    private function publishedQb()
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.published = :true')
            ->setParameter('true', true)
            ->orderBy('a.publishedAt', 'DESC');
    }

    /** @return Paginator<Article> */
    public function paginatePublished(int $page = 1, int $perPage = 9): Paginator
    {
        $qb = $this->publishedQb()
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        return new Paginator($qb->getQuery(), fetchJoinCollection: false);
    }

    public function paginateByCategory(ArticleCategory $category, int $page = 1, int $perPage = 9): Paginator
    {
        $qb = $this->publishedQb()
            ->andWhere('a.category = :category')
            ->setParameter('category', $category)
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        return new Paginator($qb->getQuery(), fetchJoinCollection: false);
    }

    public function findOnePublishedBySlug(string $slug): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.slug = :slug')
            ->andWhere('a.published = :true')
            ->setParameter('slug', $slug)
            ->setParameter('true', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** @return Article[] */
    public function findRecent(int $limit = 5, ?Article $excludeArticle = null): array
    {
        $qb = $this->publishedQb()->setMaxResults($limit);
        if ($excludeArticle) {
            $qb->andWhere('a.id != :id')->setParameter('id', $excludeArticle->getId());
        }
        return $qb->getQuery()->getResult();
    }

    /** @return Article[] */
    public function findAllPublished(): array
    {
        return $this->publishedQb()->getQuery()->getResult();
    }
}
