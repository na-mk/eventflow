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

class RegistrationController extends AbstractController
{
    // POST /api/events/{id}/register — s'inscrire à un événement
    #[Route('/api/events/{id}/register', name: 'api_register_event', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function register(
        int $id,
        EntityManagerInterface $em,
        RegistrationRepository $registrationRepo
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $event = $em->getRepository(Event::class)->find($id);
        if (!$event) {
            return $this->json(['message' => 'Event not found'], 404);
        }

        if (!$event->isPublished()) {
            return $this->json(['message' => 'Event is not published'], 403);
        }

        // Vérifier si déjà inscrit
        $existing = $registrationRepo->findByUserAndEvent($user->getId(), $id);
        if ($existing && $existing->getStatus() !== Registration::STATUS_CANCELLED) {
            return $this->json(['message' => 'Already registered to this event'], 409);
        }

        // Vérifier capacité
        if ($event->isFull()) {
            return $this->json(['message' => 'Event is full'], 422);
        }

        if ($registrationRepo->hasScheduleConflictForUser(
            $user->getId(),
            $event->getEventDate(),
            $event->getEndDate(),
            $event->getId()
        )) {
            return $this->json(['message' => 'You are already registered for another event at the same time'], 409);
        }

        $registration = $existing ?? new Registration();
        if ($existing === null) {
            $registration->setUser($user);
            $registration->setEvent($event);
            $em->persist($registration);
        } else {
            $registration->setRegisteredAt(new \DateTimeImmutable());
        }
        $registration->setStatus(Registration::STATUS_CONFIRMED);

        $em->flush();

        return $this->json($this->serialize($registration), $existing === null ? 201 : 200);
    }

    // DELETE /api/registrations/{id} — annuler sa propre inscription
    #[Route('/api/registrations/{id}', name: 'api_unregister_event', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function unregister(
        int $id,
        EntityManagerInterface $em,
        RegistrationRepository $registrationRepo
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $registration = $registrationRepo->find($id);
        if (!$registration) {
            return $this->json(['message' => 'Registration not found'], 404);
        }

        if ($registration->getUser()?->getId() !== $user->getId()) {
            return $this->json(['message' => 'Forbidden'], 403);
        }

        $registration->setStatus(Registration::STATUS_CANCELLED);
        $em->flush();

        return $this->json(['message' => 'Successfully unregistered']);
    }

    // GET /api/registrations/my — mes inscriptions
    #[Route('/api/registrations/my', name: 'api_my_registrations', methods: ['GET'])]
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
                'id'              => $r->getEvent()?->getId(),
                'title'           => $r->getEvent()?->getTitle(),
                'eventDate'       => $r->getEvent()?->getEventDate()?->format('c'),
                'location'        => $r->getEvent()?->getLocation(),
                'remainingPlaces' => $r->getEvent()?->getRemainingPlaces(),
            ],
        ];
    }
}
