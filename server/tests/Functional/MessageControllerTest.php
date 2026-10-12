<?php

namespace App\Tests\Functional;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MessageControllerTest extends WebTestCase
{
    public function testAnonymousUserCannotCreateMessage(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/new_message',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'idProject' => 1,
                'content' => 'Test message',
            ])
        );

        self::assertResponseStatusCodeSame(401);
    }

    public function testAuthenticatedUserCannotCreateMessageWithoutRequiredFields(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $jwtManager = $container->get(JWTTokenManagerInterface::class);
        $user = $this->createUser($entityManager, ['ROLE_USER']);

        try {
            $client->request(
                'POST',
                '/api/new_message',
                [],
                [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_AUTHORIZATION' => 'Bearer ' . $jwtManager->create($user),
                ],
                json_encode(['content' => 'Message without project'])
            );

            self::assertResponseStatusCodeSame(400);
            self::assertSame(['error' => 'Missing fields'], json_decode($client->getResponse()->getContent(), true));
        } finally {
            $this->removeUser($entityManager, $user);
        }
    }

    public function testAuthenticatedUserCanCreateMessage(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);
        $jwtManager = $container->get(JWTTokenManagerInterface::class);
        $user = $this->createUser($entityManager, ['ROLE_USER']);
        $content = 'Functional test ' . uniqid();
        $messageId = null;

        try {
            $client->request(
                'POST',
                '/api/new_message',
                [],
                [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_AUTHORIZATION' => 'Bearer ' . $jwtManager->create($user),
                ],
                json_encode([
                    'idProject' => 1,
                    'content' => $content,
                ])
            );

            self::assertResponseStatusCodeSame(201);

            $response = json_decode(
                $client->getResponse()->getContent(),
                true
            );

            self::assertSame('Message created', $response['status']);
            self::assertArrayHasKey('id', $response);
            $messageId = $response['id'];
            $entityManager->clear();
            $message = $entityManager->getRepository(Message::class)->find($messageId);
            self::assertNotNull($message);
            self::assertSame($content, $message->getText());
            self::assertSame(1, $message->getIdProject());
            self::assertSame($user->getId(), $message->getIdUser());
            self::assertInstanceOf(\DateTimeImmutable::class, $message->getCreatedAt());
        } finally {
            $this->removeMessageAndUser($entityManager, $messageId, $user->getId());
        }
    }

    public function testRegularUserCannotDeleteMessage(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $jwtManager = $container->get(JWTTokenManagerInterface::class);
        $user = $this->createUser($entityManager, ['ROLE_USER']);

        try {
            $client->request(
                'DELETE',
                '/api/message/999999999',
                [],
                [],
                [
                    'HTTP_AUTHORIZATION' => 'Bearer ' . $jwtManager->create($user),
                ]
            );

            self::assertResponseStatusCodeSame(403);
        } finally {
            $this->removeUser($entityManager, $user);
        }
    }

    public function testAdminGetsNotFoundWhenDeletingUnknownMessage(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $jwtManager = $container->get(JWTTokenManagerInterface::class);
        $admin = $this->createUser($entityManager, ['ROLE_ADMIN']);

        try {
            $client->request(
                'DELETE',
                '/api/message/999999999',
                [],
                [],
                [
                    'HTTP_AUTHORIZATION' => 'Bearer ' . $jwtManager->create($admin),
                ]
            );

            self::assertResponseStatusCodeSame(404);
        } finally {
            $this->removeUser($entityManager, $admin);
        }
    }

    private function createUser(EntityManagerInterface $entityManager, array $roles): User 
    {
        $user = new User();
        $user->setEmail('message-test-' . uniqid() . '@example.test');
        $user->setPassword('test-password-hash');
        $user->setRoles($roles);
        $user->setFirstname('Functional');
        $user->setLastname('Test');
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    private function removeUser(EntityManagerInterface $entityManager, User $user): void 
    {
        $managedUser = $entityManager->find(User::class, $user->getId());

        if ($managedUser !== null) {
            $entityManager->remove($managedUser);
            $entityManager->flush();
        }
    }

    private function removeMessageAndUser(EntityManagerInterface $entityManager, ?int $messageId, ?int $userId): void 
    {
        $message = $messageId !== null ? $entityManager->find(Message::class, $messageId) : null;

        if ($message !== null) {
            $entityManager->remove($message);
            $entityManager->flush();
        }

        $user = $userId !== null ? $entityManager->find(User::class, $userId) : null;

        if ($user !== null) {
            $entityManager->remove($user);
            $entityManager->flush();
        }
    }
}