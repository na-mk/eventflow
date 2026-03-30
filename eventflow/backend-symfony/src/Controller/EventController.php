<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Security\EventVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/events')]
class EventController extends AbstractController
{
    // GET /api/events — liste publique (événements publiés)
    #[Route('', name: 'api_events_list', methods: ['GET'])]
    public function list(EventRepository $repo): JsonResponse
    {
        $events = $repo->findPublished();
        return $this->json(array_map([$this, 'serializeEvent'], $events));
    }

    // GET /api/events/all — liste complète (admin/organizer)
    #[Route('/all', name: 'api_events_all', methods: ['GET'])]
    public function listAll(EventRepository $repo): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $events = $repo->findAll();
        } else {
            $events = $repo->findByOrganizer($user->getId());
        }

        return $this->json(array_map([$this, 'serializeEvent'], $events));
    }

    // GET /api/events/{id}
    #[Route('/{id}', name: 'api_events_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Event $event): JsonResponse
    {
        return $this->json($this->serializeEvent($event));
    }

    // POST /api/events
    #[Route('', name: 'api_events_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        if (!in_array('ROLE_ORGANIZER', $user->getRoles()) && !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['message' => 'Forbidden: only organizers and admins can create events'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['message' => 'Invalid JSON'], 400);
        }

        $event = new Event();
        $event->setTitle($data['title'] ?? '');
        $event->setDescription($data['description'] ?? '');
        $event->setLocation($data['location'] ?? '');
        $event->setMaxParticipants((int)($data['maxParticipants'] ?? 0));
        $event->setOrganizer($user);
        $event->setIsPublished($data['isPublished'] ?? false);

        try {
            $date = new \DateTime($data['eventDate'] ?? '');
            $event->setEventDate($date);
        } catch (\Exception) {
            return $this->json(['errors' => ['eventDate' => 'Invalid date format']], 422);
        }

        $errors = $validator->validate($event);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $messages], 422);
        }

        $em->persist($event);
        $em->flush();

        return $this->json($this->serializeEvent($event), 201);
    }

    // PUT /api/events/{id}
    #[Route('/{id}', name: 'api_events_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(
        Event $event,
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $this->denyAccessUnlessGranted(EventVoter::EDIT, $event);

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['message' => 'Invalid JSON'], 400);
        }

        if (isset($data['title']))           $event->setTitle($data['title']);
        if (isset($data['description']))     $event->setDescription($data['description']);
        if (isset($data['location']))        $event->setLocation($data['location']);
        if (isset($data['maxParticipants'])) $event->setMaxParticipants((int)$data['maxParticipants']);
        if (isset($data['isPublished']))     $event->setIsPublished((bool)$data['isPublished']);
        if (isset($data['eventDate'])) {
            try {
                $event->setEventDate(new \DateTime($data['eventDate']));
            } catch (\Exception) {
                return $this->json(['errors' => ['eventDate' => 'Invalid date format']], 422);
            }
        }

        $errors = $validator->validate($event);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $messages], 422);
        }

        $em->flush();
        return $this->json($this->serializeEvent($event));
    }

    // DELETE /api/events/{id}
    #[Route('/{id}', name: 'api_events_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(Event $event, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted(EventVoter::DELETE, $event);
        $em->remove($event);
        $em->flush();
        return $this->json(['message' => 'Event deleted'], 200);
    }

    // PATCH /api/events/{id}/publish
    #[Route('/{id}/publish', name: 'api_events_publish', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function publish(Event $event, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted(EventVoter::PUBLISH, $event);
        $event->setIsPublished(!$event->isPublished());
        $em->flush();
        return $this->json($this->serializeEvent($event));
    }

    private function serializeEvent(Event $event): array
    {
        return [
            'id'                 => $event->getId(),
            'title'              => $event->getTitle(),
            'description'        => $event->getDescription(),
            'eventDate'          => $event->getEventDate()?->format('c'),
            'location'           => $event->getLocation(),
            'maxParticipants'    => $event->getMaxParticipants(),
            'isPublished'        => $event->isPublished(),
            'createdAt'          => $event->getCreatedAt()?->format('c'),
            'participantsCount'  => $event->getConfirmedParticipantsCount(),
            'isFull'             => $event->isFull(),
            'organizer'          => [
                'id'        => $event->getOrganizer()?->getId(),
                'firstName' => $event->getOrganizer()?->getFirstName(),
                'lastName'  => $event->getOrganizer()?->getLastName(),
            ],
        ];
    }
}
