<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tag>
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /** @return Tag[] */
    public function findAllWithPhotoCount(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.photos', 'p')
            ->groupBy('t.id')
            ->having('COUNT(p.id) > 0')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneBySlug(string $slug): ?Tag
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
