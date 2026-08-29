<?php

namespace App\Security;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EventVoter extends Voter
{
    public const EDIT   = 'EVENT_EDIT';
    public const DELETE = 'EVENT_DELETE';
    public const PUBLISH = 'EVENT_PUBLISH';
    public const VIEW = 'EVENT_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE, self::PUBLISH, self::VIEW], true)
            && $subject instanceof Event;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Event $event */
        $event = $subject;

        // Admin peut tout faire
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Organizer peut modifier/supprimer/publier ses propres événements
        return match ($attribute) {
            self::EDIT, self::DELETE, self::PUBLISH, self::VIEW => $event->getOrganizer()?->getId() === $user->getId(),
            default => false,
        };
    }
}
