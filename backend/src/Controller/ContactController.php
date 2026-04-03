<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use App\Entity\User;
use App\Repository\ContactMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/contact')]
class ContactController extends AbstractController
{
    private const ALLOWED_STATUSES = [
        ContactMessage::STATUS_NEW,
        ContactMessage::STATUS_READ,
        ContactMessage::STATUS_PROCESSED,
    ];

    #[Route('', name: 'api_contact_list', methods: ['GET'])]
    public function list(ContactMessageRepository $repository): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User || !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->json(['message' => 'Forbidden'], 403);
        }

        $messages = $repository->findBy([], ['createdAt' => 'DESC']);

        return $this->json(array_map(static fn (ContactMessage $message) => [
            'id' => $message->getId(),
            'name' => $message->getName(),
            'email' => $message->getEmail(),
            'subject' => $message->getSubject(),
            'message' => $message->getMessage(),
            'status' => $message->getStatus(),
            'createdAt' => $message->getCreatedAt()?->format('c'),
        ], $messages));
    }

    #[Route('/{id}/status', name: 'api_contact_update_status', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function updateStatus(
        ContactMessage $message,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User || !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->json(['message' => 'Forbidden'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $status = $data['status'] ?? null;

        if (!is_string($status) || !in_array($status, self::ALLOWED_STATUSES, true)) {
            return $this->json(['message' => 'Invalid status'], 422);
        }

        $message->setStatus($status);
        $em->flush();

        return $this->json([
            'message' => 'Status updated successfully',
            'contactMessage' => [
                'id' => $message->getId(),
                'status' => $message->getStatus(),
            ],
        ]);
    }

    #[Route('', name: 'api_contact_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['message' => 'Invalid JSON'], 400);
        }

        $message = new ContactMessage();
        $message
            ->setName(trim((string) ($data['name'] ?? '')))
            ->setEmail(trim((string) ($data['email'] ?? '')))
            ->setSubject(trim((string) ($data['subject'] ?? '')))
            ->setMessage(trim((string) ($data['message'] ?? '')));

        $errors = $validator->validate($message);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json(['errors' => $messages], 422);
        }

        $em->persist($message);
        $em->flush();

        return $this->json([
            'message' => 'Message sent successfully',
            'contactMessage' => [
                'id' => $message->getId(),
                'status' => $message->getStatus(),
                'createdAt' => $message->getCreatedAt()?->format('c'),
            ],
        ], 201);
    }
}
