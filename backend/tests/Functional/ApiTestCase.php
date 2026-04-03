<?php

namespace App\Tests\Functional;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;
    protected UserPasswordHasherInterface $hasher;

    protected function setUp(): void
    {
        parent::setUp();

        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $this->resetDatabase();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em->clear();
        unset($this->em, $this->hasher, $this->client);
    }

    protected function resetDatabase(): void
    {
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        if ($metadata === []) {
            return;
        }

        $tool = new SchemaTool($this->em);
        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);
    }

    protected function jsonRequest(string $method, string $uri, ?array $payload = null, array $server = []): void
    {
        $content = $payload === null ? null : json_encode($payload, JSON_THROW_ON_ERROR);

        $this->client->request(
            $method,
            $uri,
            [],
            [],
            array_merge([
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ], $server),
            $content
        );
    }

    protected function createUser(array $overrides = []): User
    {
        $counter = random_int(1000, 99999);

        $user = new User();
        $user
            ->setEmail($overrides['email'] ?? "user{$counter}@eventflow.test")
            ->setFirstName($overrides['firstName'] ?? 'Test')
            ->setLastName($overrides['lastName'] ?? 'User')
            ->setPhone($overrides['phone'] ?? '0600000000')
            ->setRoles([$overrides['role'] ?? 'participant'])
            ->setConsentDate($overrides['consentDate'] ?? new \DateTimeImmutable())
            ->setConsentVersion($overrides['consentVersion'] ?? '1.0')
            ->setIsAnonymized($overrides['isAnonymized'] ?? false)
            ->setCreatedAt($overrides['createdAt'] ?? new \DateTimeImmutable());

        $plainPassword = $overrides['plainPassword'] ?? 'Password123!';
        $user->setPassword($this->hasher->hashPassword($user, $plainPassword));

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    protected function createEvent(User $organizer, array $overrides = []): Event
    {
        $event = new Event();
        $event
            ->setTitle($overrides['title'] ?? 'Event Test')
            ->setDescription($overrides['description'] ?? 'Description de test')
            ->setEventDate($overrides['eventDate'] ?? new \DateTimeImmutable('+2 days'))
            ->setEndDate($overrides['endDate'] ?? new \DateTimeImmutable('+2 days +2 hours'))
            ->setLocation($overrides['location'] ?? 'Paris')
            ->setMaxParticipants($overrides['maxParticipants'] ?? 20)
            ->setOrganizer($organizer)
            ->setIsPublished($overrides['isPublished'] ?? true);

        $this->em->persist($event);
        $this->em->flush();

        return $event;
    }

    protected function authHeaders(User $user): array
    {
        $tokenManager = static::getContainer()->get(JWTTokenManagerInterface::class);

        return [
            'HTTP_AUTHORIZATION' => 'Bearer '.$tokenManager->create($user),
        ];
    }
}
