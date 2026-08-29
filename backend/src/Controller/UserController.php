<?php

namespace App\Controller;

use App\Entity\ConsentLog;
use App\Entity\User;
use App\Repository\ConsentLogRepository;
use App\Repository\EventRepository;
use App\Repository\RegistrationRepository;
use App\Service\ConsentLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/me')]
class UserController extends AbstractController
{
    // GET /api/me — profil complet
    #[Route('', name: 'api_me_show', methods: ['GET'])]
    public function show(ConsentLogRepository $clRepo): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json($this->serializeUser($user));
    }

    // PUT /api/me — rectification des données personnelles (RGPD Art.16)
    #[Route('', name: 'api_me_update', methods: ['PUT'])]
    public function update(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        ConsentLogService $consentLogService
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->isAnonymized()) {
            return $this->json(['message' => 'Account is anonymized'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['message' => 'Invalid JSON'], 400);
        }

        if (isset($data['firstName'])) $user->setFirstName($data['firstName']);
        if (isset($data['lastName']))  $user->setLastName($data['lastName']);
        if (isset($data['phone']))     $user->setPhone($data['phone']);

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $messages], 422);
        }

        $em->flush();

        // Log RGPD : rectification
        $consentLogService->log(
            $user,
            ConsentLog::ACTION_DATA_ACCESSED,
            $request->getClientIp(),
            'User data rectification'
        );

        return $this->json($this->serializeUser($user));
    }

    // DELETE /api/me — anonymisation (RGPD Art.17 - droit à l'oubli)
    #[Route('', name: 'api_me_anonymize', methods: ['DELETE'])]
    public function anonymize(
        Request $request,
        EntityManagerInterface $em,
        ConsentLogService $consentLogService,
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->isAnonymized()) {
            return $this->json(['message' => 'Account already anonymized'], 409);
        }

        // Log avant anonymisation
        $consentLogService->log(
            $user,
            ConsentLog::ACTION_DATA_DELETED,
            $request->getClientIp(),
            'User requested account anonymization'
        );

        // Anonymisation des données personnelles
        $user->setEmail(hash('sha256', (string) $user->getEmail()));
        $user->setFirstName('Utilisateur supprimé');
        $user->setLastName('Compte supprimé');
        $user->setPhone(null);
        $user->setPassword($hasher->hashPassword($user, bin2hex(random_bytes(32))));
        $user->setRoles([User::ROLE_PARTICIPANT]);
        $user->setIsAnonymized(true);
        $user->setConsentDate(null);

        $em->flush();

        return $this->json(['message' => 'Account anonymized successfully']);
    }

    // GET /api/me/export — export données personnelles (bonus RGPD portabilité)
    #[Route('/export', name: 'api_me_export', methods: ['GET'])]
    public function export(
        ConsentLogRepository $clRepo,
        RegistrationRepository $registrationRepo,
        EventRepository $eventRepo,
        Request $request,
        ConsentLogService $consentLogService
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $consentLogService->log(
            $user,
            ConsentLog::ACTION_DATA_ACCESSED,
            $request->getClientIp(),
            'User exported personal data'
        );

        $logs = $clRepo->findByUser($user->getId());
        $registrations = $registrationRepo->findBy(['user' => $user]);
        $organizedEvents = $eventRepo->findByOrganizer($user->getId());

        return $this->json([
            'personalData' => $this->serializeUser($user),
            'consentLogs'  => array_map(fn($l) => [
                'action'    => $l->getAction(),
                'timestamp' => $l->getTimestamp()?->format('c'),
                'details'   => $l->getDetails(),
            ], $logs),
            'registrations' => array_map(fn($registration) => [
                'id' => $registration->getId(),
                'status' => $registration->getStatus(),
                'registeredAt' => $registration->getRegisteredAt()?->format('c'),
                'event' => [
                    'id' => $registration->getEvent()?->getId(),
                    'title' => $registration->getEvent()?->getTitle(),
                    'eventDate' => $registration->getEvent()?->getEventDate()?->format('c'),
                    'location' => $registration->getEvent()?->getLocation(),
                ],
            ], $registrations),
            'organizedEvents' => array_map(fn($event) => [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'eventDate' => $event->getEventDate()?->format('c'),
                'endDate' => $event->getEndDate()?->format('c'),
                'location' => $event->getLocation(),
                'maxParticipants' => $event->getMaxParticipants(),
                'isPublished' => $event->isPublished(),
                'createdAt' => $event->getCreatedAt()?->format('c'),
            ], $organizedEvents),
        ]);
    }

    // POST /api/me/password — changement de mot de passe
    #[Route('/password', name: 'api_me_password', methods: ['POST'])]
    public function changePassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        if (empty($data['currentPassword']) || empty($data['newPassword'])) {
            return $this->json(['message' => 'currentPassword and newPassword required'], 400);
        }

        if (!$hasher->isPasswordValid($user, $data['currentPassword'])) {
            return $this->json(['message' => 'Current password is incorrect'], 401);
        }

        if (strlen($data['newPassword']) < 6) {
            return $this->json(['errors' => ['newPassword' => 'Password must be at least 6 characters']], 422);
        }

        $user->setPassword($hasher->hashPassword($user, $data['newPassword']));
        $em->flush();

        return $this->json(['message' => 'Password updated successfully']);
    }

    private function serializeUser(User $user): array
    {
        return [
            'id'             => $user->getId(),
            'email'          => $user->getEmail(),
            'firstName'      => $user->getFirstName(),
            'lastName'       => $user->getLastName(),
            'phone'          => $user->getPhone(),
            'roles'          => $user->getRoles(),
            'consentDate'    => $user->getConsentDate()?->format('c'),
            'consentVersion' => $user->getConsentVersion(),
            'isAnonymized'   => $user->isAnonymized(),
            'createdAt'      => $user->getCreatedAt()?->format('c'),
        ];
    }
}
