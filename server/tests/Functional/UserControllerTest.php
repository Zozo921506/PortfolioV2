<?php

namespace App\Tests\Functional;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserControllerTest extends WebTestCase
{
    public function testRegisterCreatesUser(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);

        $email = 'register-' . uniqid() . '@example.test';

        try {
            $client->request(
                'POST',
                '/api/register',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode([
                    'email' => $email,
                    'password' => 'Strong-test-password-123!',
                    'firstname' => 'Functional',
                    'lastname' => 'Test',
                ])
            );

            self::assertResponseStatusCodeSame(201);
            self::assertSame(
                ['message' => 'User created'],
                json_decode($client->getResponse()->getContent(), true)
            );

            $user = $entityManager->getRepository(User::class)
                ->findOneBy(['email' => $email]);

            self::assertNotNull($user);
            self::assertSame('Functional', $user->getFirstname());
            self::assertSame('Test', $user->getLastname());
            self::assertContains('ROLE_USER', $user->getRoles());
            self::assertNotSame(
                'Strong-test-password-123!',
                $user->getPassword()
            );

            $hasher = $container->get(UserPasswordHasherInterface::class);
            self::assertTrue(
                $hasher->isPasswordValid($user, 'Strong-test-password-123!')
            );
        } finally {
            $user = $entityManager->getRepository(User::class)
                ->findOneBy(['email' => $email]);

            if ($user !== null) {
                $entityManager->remove($user);
                $entityManager->flush();
            }
        }
    }

    public function testRegisterRejectsInvalidEmail(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'not-an-email',
                'password' => 'password123',
                'firstname' => 'Test',
                'lastname' => 'User',
            ])
        );

        self::assertResponseStatusCodeSame(400);
        self::assertSame(
            ['error' => 'Format invalid'],
            json_decode($client->getResponse()->getContent(), true)
        );
    }

    public function testRegisterRejectsMissingEmail(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'password' => 'password123',
                'firstname' => 'Test',
                'lastname' => 'User',
            ])
        );

        // Le code actuel appelle trim(null) avant sa validation.
        // Ce test révèle le comportement réel du contrôleur.
        self::assertResponseStatusCodeSame(400);
    }

    public function testLoginReturnsJwtForValidCredentials(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $email = 'login-' . uniqid() . '@example.test';
        $plainPassword = 'Strong-test-password-123!';

        $user = new User();
        $user->setEmail($email);
        $user->setFirstname('Login');
        $user->setLastname('Test');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($hasher->hashPassword($user, $plainPassword));

        $entityManager->persist($user);
        $entityManager->flush();

        try {
            $client->request(
                'POST',
                '/api/login',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode([
                    'email' => $email,
                    'password' => $plainPassword,
                ])
            );

            self::assertResponseIsSuccessful();

            $response = json_decode($client->getResponse()->getContent(), true);

            self::assertArrayHasKey('token', $response);
            self::assertStringStartsWith('Bearer ', $response['token']);
            self::assertNotSame('Bearer ', $response['token']);
        } finally {
            $user = $entityManager->getRepository(User::class)
                ->findOneBy(['email' => $email]);

            if ($user !== null) {
                $entityManager->remove($user);
                $entityManager->flush();
            }
        }
    }

    public function testLoginRejectsWrongPassword(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $email = 'wrong-password-' . uniqid() . '@example.test';

        $user = new User();
        $user->setEmail($email);
        $user->setFirstname('Login');
        $user->setLastname('Test');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword(
            $hasher->hashPassword($user, 'Correct-password-123!')
        );

        $entityManager->persist($user);
        $entityManager->flush();

        try {
            $client->request(
                'POST',
                '/api/login',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode([
                    'email' => $email,
                    'password' => 'Wrong-password-123!',
                ])
            );

            self::assertResponseStatusCodeSame(401);
            self::assertSame(
                ['error' => 'Wrong password'],
                json_decode($client->getResponse()->getContent(), true)
            );
        } finally {
            $user = $entityManager->getRepository(User::class)
                ->findOneBy(['email' => $email]);

            if ($user !== null) {
                $entityManager->remove($user);
                $entityManager->flush();
            }
        }
    }

    public function testLoginRejectsUnknownUser(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'unknown-' . uniqid() . '@example.test',
                'password' => 'Any-password-123!',
            ])
        );

        self::assertResponseStatusCodeSame(401);
        self::assertSame(
            ['error' => 'User not found'],
            json_decode($client->getResponse()->getContent(), true)
        );
    }
}