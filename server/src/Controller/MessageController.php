<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Message;
use App\Repository\MessageRepository;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
// use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
// use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class MessageController extends AbstractController
{
    #[Route('/api/new_message', name: 'create_message', methods: ['POST'])]
    public function newMessage(Request $request, EntityManagerInterface $entity)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        if (!$request->request->get('idProject') || !$request->request->get('content')) {
            return new JsonResponse(['error' => 'Missing fields'], Response::HTTP_BAD_REQUEST);
        }

        $message = new Message();
        $message->setIdProject($request->request->get('idProject'));
        $message->setText($request->request->get('content'));
        $message->setIdUser($user->getId());
        $message->setCreatedAt(new \DateTimeImmutable('now'));
        $entity->persist($message);
        $entity->flush();
        return new JsonResponse(['status' => 'Message created', 'id' => $message->getId()], Response::HTTP_CREATED);
    }

    #[Route('/api/message/{id}', methods: ['DELETE'])]
    public function removeMessage(Request $request, EntityManagerInterface $entity, MessageRepository $repository, $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $message = $repository->find($id);
        if (!$message)
        {
            return new JsonResponse(['error' => 'Inexistant project'], Response::HTTP_NOT_FOUND);
        }

        $entity->remove($message);
        $entity->flush();
        return new JsonResponse(['status' => 'Message deleted'], Response::HTTP_NO_CONTENT);
    }
}
