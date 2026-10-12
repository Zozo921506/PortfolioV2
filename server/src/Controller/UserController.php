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
        if (!is_array($data)) {
            return new JsonResponse(
                ['error' => 'Invalid JSON'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!is_string($email) || !is_string($password)) 
        {
            return new JsonResponse(['error' => 'Required fields must be strings'], Response::HTTP_BAD_REQUEST);
        }

        $email = trim($email);
        $password = trim($password);

        if ($email === '') 
        { 
            return new JsonResponse( ['error' => 'Email is required'], Response::HTTP_BAD_REQUEST ); 
        } 
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
        { 
            return new JsonResponse( ['error' => 'Format invalid'], Response::HTTP_BAD_REQUEST ); 
        } 
        
        if ($password === '') 
        { 
            return new JsonResponse( ['error' => 'Password is required'], Response::HTTP_BAD_REQUEST ); 
        } 

        try
        {
            $user = $userProvider->loadUserByIdentifier($email);
            if (!$user instanceof PasswordAuthenticatedUserInterface)
            {
                throw new \LogicException("User can't connect with password");
            }
        }
        catch(AuthenticationException $e)
        {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$passwordHasher->isPasswordValid($user, $password))
        {
            return new JsonResponse(['error' => 'Wrong password'], Response::HTTP_UNAUTHORIZED);
        }

        $token = $jwtManager->create($user);
        $token = "Bearer ". $token;
        return new JsonResponse(['token' => $token], Response::HTTP_OK);
    }

    #[Route('/api/register', methods:['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entity): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return new JsonResponse(
                ['error' => 'Invalid JSON'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $firstname = $data['firstname'] ?? null;
        $lastname = $data['lastname'] ?? null;

        if (!is_string($email) || !is_string($password) || !is_string($firstname) || !is_string($lastname)) 
        {
            return new JsonResponse(['error' => 'Required fields must be strings'], Response::HTTP_BAD_REQUEST);
        }

        $email = trim($email);
        $password = trim($password);
        $firstname = trim($firstname);
        $lastname = trim($lastname);

        if ($email === '') 
        { 
            return new JsonResponse( ['error' => 'Email is required'], Response::HTTP_BAD_REQUEST ); 
        } 
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
        { 
            return new JsonResponse( ['error' => 'Format invalid'], Response::HTTP_BAD_REQUEST ); 
        } 
        
        if ($password === '') 
        { 
            return new JsonResponse( ['error' => 'Password is required'], Response::HTTP_BAD_REQUEST ); 
        } 
        
        if ($firstname === '') 
        { 
            return new JsonResponse( ['error' => 'Firstname is required'], Response::HTTP_BAD_REQUEST ); 
        } 
        
        if ($lastname === '') 
        { 
            return new JsonResponse( ['error' => 'Lastname is required'], Response::HTTP_BAD_REQUEST ); 
        }

        $user = new User();
        // return new JsonResponse(['test' => $user->getId()]);
        $adminMail = $this->getParameter('app.ADMIN_MAIL');
        $user->setEmail($email);
        if ($email === $adminMail) 
        {
            $user->setRoles(['ROLE_ADMIN']);
        } else {
            $user->setRoles(['ROLE_USER']);
        }

        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setPassword($passwordHasher->hashPassword($user, $password));
        $entity->persist($user);
        $entity->flush();
        return new JsonResponse(['message' => 'User created'], Response::HTTP_CREATED);
    }
}
