<?php

namespace App\Repository;

use App\Entity\Registration;
use DateTimeInterface;
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

    public function hasScheduleConflictForUser(
        int $userId,
        DateTimeInterface $eventStart,
        ?DateTimeInterface $eventEnd,
        ?int $excludeEventId = null
    ): bool {
        $effectiveEventEnd = $eventEnd ?? $eventStart;

        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->innerJoin('r.event', 'e')
            ->where('r.user = :userId')
            ->andWhere('r.status = :status')
            ->andWhere('e.eventDate <= :eventEnd')
            ->andWhere('(e.endDate IS NULL AND e.eventDate >= :eventStart) OR (e.endDate IS NOT NULL AND e.endDate >= :eventStart)')
            ->setParameter('userId', $userId)
            ->setParameter('status', Registration::STATUS_CONFIRMED)
            ->setParameter('eventStart', $eventStart)
            ->setParameter('eventEnd', $effectiveEventEnd);

        if ($excludeEventId !== null) {
            $qb->andWhere('e.id != :excludeEventId')
                ->setParameter('excludeEventId', $excludeEventId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }
}
