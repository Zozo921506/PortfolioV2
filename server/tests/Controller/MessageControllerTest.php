<?php

namespace App\Tests\Controller;

use App\Controller\MessageController;
use App\Entity\Message;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

final class MessageControllerTest extends TestCase
{
    private function createController(bool $isGranted = true): MessageController
    {
        $controller = new MessageController();
        $authorizationChecker = $this->createMock(\Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface::class);
        $authorizationChecker->method('isGranted')->willReturn($isGranted);
        $container = new ContainerBuilder();
        $container->set('security.authorization_checker',$authorizationChecker);
        $tokenStorage = $this->createMock(\Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface::class);
        $container->set('security.token_storage', $tokenStorage);
        $controller->setContainer($container);
        return $controller;
    }

    public function testNewMessageRejectsMissingFields(): void
    {
        $controller = $this->createController();
        $entity = $this->createMock(EntityManagerInterface::class);
        $request = Request::create(
            '/api/new_message',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['idProject' => 1])
        );

        $response = $controller->newMessage($request, $entity);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode()
        );

        $this->assertSame(['error' => 'Missing fields'],json_decode($response->getContent(), true));
    }

    public function testNewMessageRejectsUnauthenticatedUser(): void
    {
        $controller = $this->createController(false);
        $entity = $this->createMock(EntityManagerInterface::class);
        $request = Request::create(
            '/api/new_message',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'idProject' => 1,
                'content' => 'Hello',
            ])
        );

        $this->expectException(AccessDeniedException::class);
        $controller->newMessage($request, $entity);
    }

    
    public function testNewMessageCreatesMessage(): void
    {
        $controller = $this->createController();
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setFirstname('Test');
        $user->setLastname('User');
        $token = new UsernamePasswordToken(
            $user,
            'main',
            ['ROLE_USER']
        );

        $tokenStorage = $this->createMock(\Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);
        $container = new ContainerBuilder();
        $authorizationChecker = $this->createMock(\Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface::class);
        $authorizationChecker->method('isGranted')->willReturn(true);
        $container->set('security.authorization_checker', $authorizationChecker);
        $container->set('security.token_storage', $tokenStorage);
        $controller->setContainer($container);
        $entity = $this->createMock(EntityManagerInterface::class);
        $entity->expects($this->once())->method('persist')->with($this->callback(function (Message $message) {
            $this->assertSame(42, $message->getIdProject());
            $this->assertSame('Mon nouveau message', $message->getText());
            $this->assertSame(1, $message->getIdUser());
            $this->assertInstanceOf(\DateTimeImmutable::class, $message->getCreatedAt());
            return true;
        }));

        $entity->expects($this->once())->method('flush');
        $reflection = new \ReflectionProperty(User::class, 'id');
        $reflection->setValue($user, 1);
        $request = Request::create(
            '/api/new_message',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'idProject' => 42,
                'content' => 'Mon nouveau message',
            ])
        );

        $response = $controller->newMessage($request, $entity);
        $this->assertSame(Response::HTTP_CREATED,$response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('Message created', $data['status']);
    }

    public function testGetProjectMessageReturnsMessages(): void
    {
        $controller = new MessageController();
        $messages = [
            ['id' => 1, 'text' => 'Premier message'],
            ['id' => 2, 'text' => 'Deuxième message'],
        ];

        $repository = $this->createMock(MessageRepository::class);
        $repository->expects($this->once())->method('findByProjectId')->with(42)->willReturn($messages);
        $response = $controller->getProjectMessage(42, $repository);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('Messages collected', $data['status']);
        $this->assertSame($messages, $data['messages']);
    }

    public function testRemoveMessageReturnsNotFound(): void
    {
        $controller = $this->createController();
        $entity = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(MessageRepository::class);
        $repository->expects($this->once())->method('find')->with('999')->willReturn(null);
        $entity->expects($this->never())->method('remove');
        $entity->expects($this->never())->method('flush');
        $request = Request::create('/api/message/999', 'DELETE');
        $response = $controller->removeMessage($request, $entity, $repository, '999');

        $this->assertSame(Response::HTTP_NOT_FOUND,$response->getStatusCode());
        $this->assertSame(['error' => 'Inexistant project'], json_decode($response->getContent(), true));
    }

    public function testRemoveMessageDeletesExistingMessage(): void
    {
        $controller = $this->createController();
        $entity = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(MessageRepository::class);
        $message = $this->createMock(Message::class);
        $repository->expects($this->once())->method('find')->with('12')->willReturn($message);
        $entity->expects($this->once())->method('remove')->with($message);
        $entity->expects($this->once())->method('flush');
        $request = Request::create('/api/message/12', 'DELETE');
        $response = $controller->removeMessage($request, $entity, $repository, '12');
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testRemoveMessageRejectsNonAdmin(): void
    {
        $controller = $this->createController(false);
        $entity = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(MessageRepository::class);
        $request = Request::create('/api/message/12', 'DELETE');
        $this->expectException(AccessDeniedException::class);
        $controller->removeMessage($request, $entity, $repository, '12');
    }
}