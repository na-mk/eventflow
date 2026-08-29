<?php

namespace App\Controller;

use App\Entity\ConsentLog;
use App\Entity\User;
use App\Repository\ConsentLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    #[Route('/register', name: 'api_auth_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        ValidatorInterface $validator,
        ConsentLogRepository $consentLogRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['message' => 'Invalid JSON'], 400);
        }

        // Vérifier consentement RGPD
        if (empty($data['consentGiven'])) {
            return $this->json(['message' => 'Consent is required (RGPD)'], 422);
        }

        $role = $this->normalizePublicRole($data);
        if ($role === null) {
            return $this->json(['errors' => ['role' => 'Only participant or organizer roles are allowed']], 422);
        }

        $user = new User();
        $user->setEmail($data['email'] ?? '');
        $user->setFirstName($data['firstName'] ?? '');
        $user->setLastName($data['lastName'] ?? '');
        $user->setPhone($data['phone'] ?? null);
        $user->setRoles([$role]);
        $user->setConsentDate(new \DateTimeImmutable());
        $user->setConsentVersion('1.0');

        // Validation
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $messages], 422);
        }

        if (empty($data['password']) || strlen($data['password']) < 6) {
            return $this->json(['errors' => ['password' => 'Password must be at least 6 characters']], 422);
        }

        // Vérifier email unique
        $existing = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existing) {
            return $this->json(['message' => 'Email already used'], 409);
        }

        $user->setPassword($hasher->hashPassword($user, $data['password']));
        $em->persist($user);

        // Log consentement RGPD
        $log = new ConsentLog();
        $log->setUser($user);
        $log->setAction(ConsentLog::ACTION_CONSENT_GIVEN);
        $log->setIpAddress($request->getClientIp());
        $log->setDetails('Registration consent v1.0');
        $em->persist($log);

        $em->flush();

        return $this->json([
            'message' => 'User created successfully',
            'user' => $this->serializeUser($user)
        ], 201);
    }

    #[Route('/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // Le comportement de connexion JWT est géré par le firewall json_login,
        // cette route ne doit pas être atteinte en production si les identifiants sont corrects.
        return $this->json(['message' => 'Login endpoint (JSON login via firewall)'], 200);
    }

    #[Route('/me', name: 'api_auth_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json([
            'user' => $this->serializeUser($user)
        ]);
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

    private function normalizePublicRole(array $data): ?string
    {
        $rawRole = $data['role'] ?? $data['roles'][0] ?? 'participant';
        $normalized = strtolower((string) $rawRole);

        return match ($normalized) {
            'role_organizer', 'organizer', 'organisateur' => 'ROLE_ORGANIZER',
            'role_user', 'role_participant', 'participant' => 'ROLE_USER',
            default => null,
        };
    }
}
