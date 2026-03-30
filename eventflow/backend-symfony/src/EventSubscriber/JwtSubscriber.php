<?php

namespace App\EventSubscriber;

use App\Entity\ConsentLog;
use App\Entity\User;
use App\Service\ConsentLogService;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class JwtSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ConsentLogService $consentLogService,
        private RequestStack $requestStack
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationSuccessEvent::class => 'onAuthenticationSuccess',
        ];
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof User) return;

        $ip = $this->requestStack->getCurrentRequest()?->getClientIp();

        $this->consentLogService->log(
            $user,
            ConsentLog::ACTION_DATA_ACCESSED,
            $ip,
            'User login'
        );
    }
}
