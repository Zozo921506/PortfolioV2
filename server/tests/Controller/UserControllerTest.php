<?php

namespace App\Tests\Controller;

use App\Controller\UserController;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

final class UserControllerTest extends TestCase
{
    private function createController(): UserController
    {
        $container = new ContainerBuilder(
            new ParameterBag(['app.ADMIN_MAIL' => 'admin@example.com',])
        );

        $container->set('parameter_bag', $container->getParameterBag());
        $controller = new UserController();
        $controller->setContainer($container);
        return $controller;
    }

    private function jsonRequest(array $data): Request
    {
        return new Request([], [], [], [], [], [], json_encode($data));
    }

    private function responseData(Response $response): array
    {
        return json_decode($response->getContent(), true);
    }

    public function testLoginRejectsInvalidEmail(): void
    {
        $controller = $this->createController();
        $jwt = $this->createMock(JWTTokenManagerInterface::class);
        $provider = $this->createMock(UserProviderInterface::class);
        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $response = $controller->login(
            $this->jsonRequest([
                'email' => 'email-invalide',
                'password' => 'secret',
            ]),
            $jwt,
            $provider,
            $hasher
        );

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertSame(['error' => 'Format invalid'], $this->responseData($response));
        $provider->expects(self::never())->method('loadUserByIdentifier');
    }

    public function testLoginRejectsUnknownUser(): void
    {
        $controller = $this->createController();
        $jwt = $this->createMock(JWTTokenManagerInterface::class);
        $provider = $this->createMock(UserProviderInterface::class);
        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $provider->expects(self::once())->method('loadUserByIdentifier')->with('unknown@example.com')->willThrowException(
            new CustomUserMessageAuthenticationException('User not found')
        );

        $response = $controller->login(
            $this->jsonRequest([
                'email' => 'unknown@example.com',
                'password' => 'secret',
            ]),
            $jwt,
            $provider,
            $hasher
        );

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertSame(['error' => 'User not found'], $this->responseData($response));
    }

    public function testLoginRejectsWrongPassword(): void
    {
        $controller = $this->createController();
        $user = $this->createMock(User::class);
        $jwt = $this->createMock(JWTTokenManagerInterface::class);
        $provider = $this->createMock(UserProviderInterface::class);
        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $provider->expects(self::once())->method('loadUserByIdentifier')->with('user@example.com')->willReturn($user);
        $hasher->expects(self::once())->method('isPasswordValid')->with($user, 'wrong-password')->willReturn(false);
        $jwt->expects(self::never())->method('create');
        $response = $controller->login(
            $this->jsonRequest([
                'email' => 'user@example.com',
                'password' => 'wrong-password',
            ]),
            $jwt,
            $provider,
            $hasher
        );

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertSame(['error' => 'Wrong password'], $this->responseData($response));
    }

    public function testLoginReturnsJwtOnSuccess(): void
    {
        $controller = $this->createController();
        $user = $this->createMock(User::class);
        $jwt = $this->createMock(JWTTokenManagerInterface::class);
        $provider = $this->createMock(UserProviderInterface::class);
        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $provider->expects(self::once())->method('loadUserByIdentifier')->with('user@example.com')->willReturn($user);
        $hasher->expects(self::once())->method('isPasswordValid')->with($user, 'correct-password')->willReturn(true);
        $jwt->expects(self::once())->method('create')->with($user)->willReturn('fake.jwt.token');
        $response = $controller->login(
            $this->jsonRequest([
                'email' => 'user@example.com',
                'password' => 'correct-password',
            ]),
            $jwt,
            $provider,
            $hasher
        );

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame(['token' => 'Bearer fake.jwt.token'], $this->responseData($response));
    }

    public function testRegisterCreatesRegularUser(): void
    {
        $controller = $this->createController();
        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $hasher->expects(self::once())->method('hashPassword')->with(self::isInstanceOf(User::class), 'secret123')->willReturn('hashed-password');
        $entityManager->expects(self::once())->method('persist')->with(self::callback(
            static function (User $user): bool {
                return $user->getEmail() === 'user@example.com'
                    && $user->getFirstname() === 'Alice'
                    && $user->getLastname() === 'Martin'
                    && $user->getRoles() === ['ROLE_USER']
                    && $user->getPassword() === 'hashed-password';
            }
        ));

        $entityManager->expects(self::once())->method('flush');
        $response = $controller->register(
            $this->jsonRequest([
                'email' => 'user@example.com',
                'password' => 'secret123',
                'firstname' => 'Alice',
                'lastname' => 'Martin',
            ]),
            $hasher,
            $entityManager
        );

        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        self::assertSame(['message' => 'User created'], $this->responseData($response));
    }

    public function testRegisterAssignsAdminRole(): void
    {
        $controller = $this->createController();
        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $hasher->method('hashPassword')->willReturn('hashed-password');
        $entityManager->expects(self::once())->method('persist')->with(self::callback(
            static function ($user): bool {
                return $user instanceof User && in_array('ROLE_ADMIN', $user->getRoles(), true);
            }
        ));

        $entityManager->expects(self::once())->method('flush');
        $response = $controller->register(
            $this->jsonRequest([
                'email' => 'admin@example.com',
                'password' => 'secret123',
                'firstname' => 'Admin',
                'lastname' => 'Test',
            ]),
            $hasher,
            $entityManager
        );

        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testRegisterRejectsInvalidEmail(): void
    {
        $controller = $this->createController();
        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('persist');
        $response = $controller->register(
            $this->jsonRequest([
                'email' => 'invalid-email',
                'password' => 'secret123',
                'firstname' => 'Alice',
                'lastname' => 'Martin',
            ]),
            $hasher,
            $entityManager
        );

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertSame(['error' => 'Format invalid'], $this->responseData($response));
    }
}