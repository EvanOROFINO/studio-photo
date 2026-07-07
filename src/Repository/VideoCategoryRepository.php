<?php

namespace App\Repository;

use App\Entity\VideoCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VideoCategory>
 */
class VideoCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VideoCategory::class);
    }

    /**
     * @return VideoCategory[]
     */
    public function findActiveOrdered(): array
    {
        return $this->findBy(['isActive' => true], ['position' => 'ASC']);
    }

    public function findBySlug(string $slug): ?VideoCategory
    {
        return $this->findOneBy(['slug' => $slug, 'isActive' => true]);
    }
}
