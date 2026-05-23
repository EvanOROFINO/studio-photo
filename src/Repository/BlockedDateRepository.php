<?php

namespace App\Repository;

use App\Entity\BlockedDate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BlockedDate>
 */
class BlockedDateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlockedDate::class);
    }

    /** @return BlockedDate[] */
    public function findOverlapping(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        // Normalize boundaries to whole days so single-day blocks created with
        // any time-of-day still match a date range probed at midnight.
        $start = $from->setTime(0, 0, 0);
        $end = $to->setTime(23, 59, 59);

        return $this->createQueryBuilder('b')
            ->andWhere('b.startDate <= :to')
            ->andWhere('COALESCE(b.endDate, b.startDate) >= :from')
            ->setParameter('from', $start)
            ->setParameter('to', $end)
            ->orderBy('b.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
