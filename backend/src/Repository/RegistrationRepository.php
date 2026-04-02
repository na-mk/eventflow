<?php

namespace App\Repository;

use App\Entity\Registration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Registration::class);
    }

    public function findByUserAndEvent(int $userId, int $eventId): ?Registration
    {
        return $this->createQueryBuilder('r')
            ->where('r.user = :userId')
            ->andWhere('r.event = :eventId')
            ->setParameter('userId', $userId)
            ->setParameter('eventId', $eventId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
