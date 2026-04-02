<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Registration;
use App\Entity\User;
use App\Repository\RegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/registrations')]
class RegistrationController extends AbstractController
{
    // POST /api/registrations/{eventId} — s'inscrire à un événement
    #[Route('/{eventId}', name: 'api_register_event', methods: ['POST'], requirements: ['eventId' => '\d+'])]
    public function register(
        int $eventId,
        EntityManagerInterface $em,
        RegistrationRepository $registrationRepo
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $event = $em->getRepository(Event::class)->find($eventId);
        if (!$event) {
            return $this->json(['message' => 'Event not found'], 404);
        }

        if (!$event->isPublished()) {
            return $this->json(['message' => 'Event is not published'], 403);
        }

        // Vérifier si déjà inscrit
        $existing = $registrationRepo->findByUserAndEvent($user->getId(), $eventId);
        if ($existing) {
            return $this->json(['message' => 'Already registered to this event'], 409);
        }

        // Vérifier capacité
        if ($event->isFull()) {
            return $this->json(['message' => 'Event is full'], 422);
        }

        $registration = new Registration();
        $registration->setUser($user);
        $registration->setEvent($event);
        $registration->setStatus(Registration::STATUS_CONFIRMED);

        $em->persist($registration);
        $em->flush();

        return $this->json($this->serialize($registration), 201);
    }

    // DELETE /api/registrations/{eventId} — se désinscrire
    #[Route('/{eventId}', name: 'api_unregister_event', methods: ['DELETE'], requirements: ['eventId' => '\d+'])]
    public function unregister(
        int $eventId,
        EntityManagerInterface $em,
        RegistrationRepository $registrationRepo
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $registration = $registrationRepo->findByUserAndEvent($user->getId(), $eventId);
        if (!$registration) {
            return $this->json(['message' => 'Registration not found'], 404);
        }

        $registration->setStatus(Registration::STATUS_CANCELLED);
        $em->flush();

        return $this->json(['message' => 'Successfully unregistered']);
    }

    // GET /api/registrations/my — mes inscriptions
    #[Route('/my', name: 'api_my_registrations', methods: ['GET'])]
    public function myRegistrations(RegistrationRepository $repo): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $registrations = $repo->findBy(['user' => $user]);

        return $this->json(array_map([$this, 'serialize'], $registrations));
    }

    private function serialize(Registration $r): array
    {
        return [
            'id'           => $r->getId(),
            'status'       => $r->getStatus(),
            'registeredAt' => $r->getRegisteredAt()?->format('c'),
            'event'        => [
                'id'        => $r->getEvent()?->getId(),
                'title'     => $r->getEvent()?->getTitle(),
                'eventDate' => $r->getEvent()?->getEventDate()?->format('c'),
                'location'  => $r->getEvent()?->getLocation(),
            ],
        ];
    }
}
