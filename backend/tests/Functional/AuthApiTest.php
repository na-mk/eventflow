<?php

namespace App\Tests\Functional;

use App\Entity\ConsentLog;
use App\Entity\User;

class AuthApiTest extends ApiTestCase
{
    public function testRegisterCreatesUserAndConsentLog(): void
    {
        $this->jsonRequest('POST', '/api/auth/register', [
            'email' => 'organizer@eventflow.test',
            'password' => 'Password123!',
            'firstName' => 'Olivia',
            'lastName' => 'Martin',
            'phone' => '0601020304',
            'role' => 'organisateur',
            'consentGiven' => true,
        ]);

        self::assertResponseStatusCodeSame(201);

        $data = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('User created successfully', $data['message']);
        self::assertContains('ROLE_ORGANIZER', $data['user']['roles']);

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'organizer@eventflow.test']);
        self::assertNotNull($user);

        $log = $this->em->getRepository(ConsentLog::class)->findOneBy(['user' => $user]);
        self::assertNotNull($log);
        self::assertSame(ConsentLog::ACTION_CONSENT_GIVEN, $log->getAction());
        self::assertNotNull($log->getIpAddress());
    }

    public function testRegisterRequiresConsent(): void
    {
        $this->jsonRequest('POST', '/api/auth/register', [
            'email' => 'participant@eventflow.test',
            'password' => 'Password123!',
            'firstName' => 'Paul',
            'lastName' => 'Durand',
            'role' => 'participant',
            'consentGiven' => false,
        ]);

        self::assertResponseStatusCodeSame(422);

        $data = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('Consent is required (RGPD)', $data['message']);
        self::assertNull($this->em->getRepository(User::class)->findOneBy(['email' => 'participant@eventflow.test']));
    }

    public function testLoginReturnsJwtToken(): void
    {
        $this->createUser([
            'email' => 'login@eventflow.test',
            'plainPassword' => 'Password123!',
            'role' => 'participant',
        ]);

        $this->jsonRequest('POST', '/api/auth/login', [
            'email' => 'login@eventflow.test',
            'password' => 'Password123!',
        ]);

        self::assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('token', $data);
        self::assertNotEmpty($data['token']);
    }

    public function testAuthenticatedUserCanExportPersonalData(): void
    {
        $user = $this->createUser([
            'email' => 'export@eventflow.test',
            'firstName' => 'Eva',
            'lastName' => 'Durand',
            'phone' => '0607080910',
            'role' => 'participant',
        ]);

        $log = new ConsentLog();
        $log
            ->setUser($user)
            ->setAction(ConsentLog::ACTION_CONSENT_GIVEN)
            ->setDetails('Initial consent');

        $this->em->persist($log);
        $this->em->flush();

        $this->jsonRequest('GET', '/api/me/export', null, $this->authHeaders($user));

        self::assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('export@eventflow.test', $data['personalData']['email']);
        self::assertSame('Eva', $data['personalData']['firstName']);
        self::assertNotEmpty($data['consentLogs']);
        self::assertContains(ConsentLog::ACTION_CONSENT_GIVEN, array_column($data['consentLogs'], 'action'));
    }
}
