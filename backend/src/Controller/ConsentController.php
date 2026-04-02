<?php

namespace App\Controller;

use App\Entity\ConsentLog;
use App\Entity\User;
use App\Service\ConsentLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/consent')]
class ConsentController extends AbstractController
{
    #[Route('', name: 'api_consent_update', methods: ['POST'])]
    public function updateConsent(
        Request $request,
        EntityManagerInterface $em,
        ConsentLogService $consentLogService
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || !array_key_exists('consentGiven', $data)) {
            return $this->json(['message' => 'consentGiven is required'], 400);
        }

        $consentGiven = (bool) $data['consentGiven'];
        $version = $data['consentVersion'] ?? '1.0';

        $user->setConsentVersion($version);

        if ($consentGiven) {
            $user->setConsentDate(new \DateTimeImmutable());
            $consentLogService->log(
                $user,
                ConsentLog::ACTION_CONSENT_GIVEN,
                $request->getClientIp(),
                "Consent updated to version {$version}"
            );
        } else {
            $user->setConsentDate(null);
            $consentLogService->log(
                $user,
                ConsentLog::ACTION_CONSENT_WITHDRAWN,
                $request->getClientIp(),
                "Consent withdrawn from version {$version}"
            );
        }

        $em->flush();

        return $this->json([
            'message' => $consentGiven ? 'Consent updated' : 'Consent withdrawn',
            'consentDate' => $user->getConsentDate()?->format('c'),
            'consentVersion' => $user->getConsentVersion(),
        ]);
    }
}
