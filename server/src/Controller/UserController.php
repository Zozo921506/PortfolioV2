<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class UserController extends AbstractController
{
    #[Route('/api/login', methods:['POST'])]
    public function login(Request $request, JWTTokenManagerInterface $jwtManager, UserProviderInterface $userProvider, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['email']) || !isset($data['password']))
        {
            return new JsonResponse(['error' => 'Email and password are required'], Response::HTTP_BAD_REQUEST);
        }

        try
        {
            $user = $userProvider->loadUserByIdentifier($data['email']);
            if (!$user instanceof PasswordAuthenticatedUserInterface)
            {
                throw new \LogicException("User can't connect with password");
            }
        }
        catch(AuthenticationException $e)
        {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$passwordHasher->isPasswordValid($user, $data['password']))
        {
            return new JsonResponse(['error' => 'Wrong password'], Response::HTTP_UNAUTHORIZED);
        }

        $token = $jwtManager->create($user);
        return new JsonResponse(['token' => $token], Response::HTTP_OK);
    }

    #[Route('/api/register', methods:['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entity): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $data = json_decode($request->getContent(), true);
        if (!isset($data['email']) || !isset($data['password']))
        {
            return new JsonResponse(['error' => 'Email and password are required'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $entity->persist($user);
        $entity->flush();
        return new JsonResponse(['message' => 'User created'], Response::HTTP_CREATED);
    }
}
