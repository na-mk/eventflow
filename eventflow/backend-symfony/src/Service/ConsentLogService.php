<?php

namespace App\Service;

use App\Entity\ConsentLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ConsentLogService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function log(User $user, string $action, ?string $ip = null, ?string $details = null): void
    {
        $log = new ConsentLog();
        $log->setUser($user);
        $log->setAction($action);
        $log->setIpAddress($ip);
        $log->setDetails($details);

        $this->em->persist($log);
        $this->em->flush();
    }
}
