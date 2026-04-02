<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findPublished(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.isPublished = true')
            ->orderBy('e.eventDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByOrganizer(int $organizerId): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.organizer = :id')
            ->setParameter('id', $organizerId)
            ->orderBy('e.eventDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
