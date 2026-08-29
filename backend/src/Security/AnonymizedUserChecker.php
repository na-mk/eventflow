<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AnonymizedUserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof User && $user->isAnonymized()) {
            throw new CustomUserMessageAccountStatusException('This account has been anonymized.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
