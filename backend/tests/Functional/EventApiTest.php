<?php

namespace App\Tests\Functional;

class EventApiTest extends ApiTestCase
{
    public function testOrganizerCanCreateEvent(): void
    {
        $organizer = $this->createUser([
            'email' => 'orga@eventflow.test',
            'role' => 'organisateur',
        ]);

        $this->jsonRequest('POST', '/api/events', [
            'title' => 'Conference Symfony',
            'description' => 'Conference technique',
            'eventDate' => (new \DateTimeImmutable('+3 days 10:00'))->format(DATE_ATOM),
            'endDate' => (new \DateTimeImmutable('+3 days 12:00'))->format(DATE_ATOM),
            'location' => 'Paris',
            'maxParticipants' => 30,
            'isPublished' => false,
        ], $this->authHeaders($organizer));

        self::assertResponseStatusCodeSame(201);

        $data = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Conference Symfony', $data['title']);
        self::assertSame(30, $data['maxParticipants']);
        self::assertSame(30, $data['remainingPlaces']);
        self::assertFalse($data['isPublished']);
    }

    public function testOrganizerCanUpdateOwnEvent(): void
    {
        $organizer = $this->createUser([
            'email' => 'owner@eventflow.test',
            'role' => 'organisateur',
        ]);
        $event = $this->createEvent($organizer, [
            'title' => 'Titre initial',
            'isPublished' => false,
        ]);

        $this->jsonRequest('PUT', '/api/events/'.$event->getId(), [
            'title' => 'Titre modifie',
            'isPublished' => true,
            'endDate' => (new \DateTimeImmutable('+2 days +4 hours'))->format(DATE_ATOM),
        ], $this->authHeaders($organizer));

        self::assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Titre modifie', $data['title']);
        self::assertTrue($data['isPublished']);
        self::assertNotEmpty($data['endDate']);
    }

    public function testParticipantCannotCreateEvent(): void
    {
        $participant = $this->createUser([
            'email' => 'participant-create@eventflow.test',
            'role' => 'participant',
        ]);

        $this->jsonRequest('POST', '/api/events', [
            'title' => 'Event interdit',
            'description' => 'Ne devrait pas etre cree',
            'eventDate' => (new \DateTimeImmutable('+5 days'))->format(DATE_ATOM),
            'location' => 'Lyon',
            'maxParticipants' => 10,
            'isPublished' => true,
        ], $this->authHeaders($participant));

        self::assertResponseStatusCodeSame(403);
    }
}
