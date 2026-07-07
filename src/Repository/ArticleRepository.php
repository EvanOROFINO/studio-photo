<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\ArticleCategory;
use App\Entity\Site;
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

    /**
     * Base query for published articles.
     * When a $site is given, only articles of that site (or with no site
     * assigned yet, for backward compatibility) are returned.
     */
    private function publishedQb(?Site $site = null)
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.published = :true')
            ->setParameter('true', true)
            ->orderBy('a.publishedAt', 'DESC');

        if ($site !== null) {
            $qb->andWhere('a.site = :site OR a.site IS NULL')
               ->setParameter('site', $site);
        }

        return $qb;
    }

    /** @return Paginator<Article> */
    public function paginatePublished(int $page = 1, int $perPage = 9, ?Site $site = null): Paginator
    {
        $qb = $this->publishedQb($site)
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        return new Paginator($qb->getQuery(), fetchJoinCollection: false);
    }

    public function paginateByCategory(ArticleCategory $category, int $page = 1, int $perPage = 9, ?Site $site = null): Paginator
    {
        $qb = $this->publishedQb($site)
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
