<?php

namespace App\Tests\Functional;

use App\Entity\ContactMessage;

class ContactApiTest extends ApiTestCase
{
    public function testVisitorCanSendContactMessage(): void
    {
        $this->jsonRequest('POST', '/api/contact', [
            'name' => 'Camille Bernard',
            'email' => 'camille@example.com',
            'subject' => 'Demande de demonstration',
            'message' => 'Bonjour, je souhaite planifier une demonstration de la plateforme EventFlow.',
        ]);

        self::assertResponseStatusCodeSame(201);

        $data = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('Message sent successfully', $data['message']);

        $message = $this->em->getRepository(ContactMessage::class)->findOneBy(['email' => 'camille@example.com']);
        self::assertNotNull($message);
        self::assertSame('Demande de demonstration', $message->getSubject());
        self::assertSame(ContactMessage::STATUS_NEW, $message->getStatus());
    }

    public function testContactMessageValidationErrorsAreReturned(): void
    {
        $this->jsonRequest('POST', '/api/contact', [
            'name' => '',
            'email' => 'invalid-email',
            'subject' => 'Hi',
            'message' => 'short',
        ]);

        self::assertResponseStatusCodeSame(422);

        $data = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('errors', $data);
        self::assertArrayHasKey('name', $data['errors']);
        self::assertArrayHasKey('email', $data['errors']);
        self::assertArrayHasKey('subject', $data['errors']);
        self::assertArrayHasKey('message', $data['errors']);
    }

    public function testAdminCanListContactMessages(): void
    {
        $admin = $this->createUser([
            'email' => 'admin-contact@eventflow.test',
            'role' => 'admin',
        ]);

        $message = new ContactMessage();
        $message
            ->setName('Nina Leroy')
            ->setEmail('nina@example.com')
            ->setSubject('Support')
            ->setMessage('Bonjour, je souhaite etre recontactee concernant un probleme de connexion.');

        $this->em->persist($message);
        $this->em->flush();

        $this->jsonRequest('GET', '/api/contact', null, $this->authHeaders($admin));

        self::assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(1, $data);
        self::assertSame('nina@example.com', $data[0]['email']);
        self::assertSame('Support', $data[0]['subject']);
        self::assertSame(ContactMessage::STATUS_NEW, $data[0]['status']);
    }

    public function testNonAdminCannotListContactMessages(): void
    {
        $participant = $this->createUser([
            'email' => 'participant-contact@eventflow.test',
            'role' => 'participant',
        ]);

        $this->jsonRequest('GET', '/api/contact', null, $this->authHeaders($participant));

        self::assertResponseStatusCodeSame(403);
    }

    public function testAdminCanUpdateContactMessageStatus(): void
    {
        $admin = $this->createUser([
            'email' => 'admin-status@eventflow.test',
            'role' => 'admin',
        ]);

        $message = new ContactMessage();
        $message
            ->setName('Lina Petit')
            ->setEmail('lina@example.com')
            ->setSubject('Question')
            ->setMessage('Bonjour, je souhaite savoir si vous proposez une version de demonstration.');

        $this->em->persist($message);
        $this->em->flush();

        $this->jsonRequest('PATCH', '/api/contact/'.$message->getId().'/status', [
            'status' => ContactMessage::STATUS_PROCESSED,
        ], $this->authHeaders($admin));

        self::assertResponseIsSuccessful();

        $this->em->refresh($message);
        self::assertSame(ContactMessage::STATUS_PROCESSED, $message->getStatus());
    }
}
