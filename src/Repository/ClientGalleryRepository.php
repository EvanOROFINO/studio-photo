<?php

namespace App\Repository;

use App\Entity\ClientGallery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClientGallery>
 */
class ClientGalleryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClientGallery::class);
    }

    public function findOneByToken(string $token): ?ClientGallery
    {
        return $this->findOneBy(['token' => $token]);
    }
}
