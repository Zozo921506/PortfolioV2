<?php

namespace App\Tests\Controller;

use App\Controller\ProjectsController;
use App\Entity\Projects;
use App\Repository\ProjectsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Vich\UploaderBundle\Handler\UploadHandler;

final class ProjectsControllerTest extends TestCase
{
    private function createController(bool $isAdmin = true): ProjectsController
    {
        $container = new ContainerBuilder();
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->method('isGranted')->with('ROLE_ADMIN')->willReturn($isAdmin);
        $container->set('security.authorization_checker', $authorizationChecker);
        $controller = new ProjectsController();
        $controller->setContainer($container);
        return $controller;
    }

    private function createRequest(
        array $data = [],
        array $files = [],
        array $headers = []
    ): Request {
        return new Request(
            [],
            $data,
            [],
            [],
            $files,
            $headers
        );
    }

    private function responseData(Response $response): ?array
    {
        $content = $response->getContent();
        if ($content === '' || $content === false) {
            return null;
        }

        return json_decode($content, true);
    }

    public function testUploadRejectsMissingFile(): void
    {
        $controller = $this->createController();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $uploadHandler = $this->createMock(\App\Service\ProjectUploadHandlerInterface::class);
        $entityManager->expects(self::never())->method('persist');
        $entityManager->expects(self::never())->method('flush');
        $response = $controller->upload(
            $this->createRequest([
                'name' => 'Portfolio',
                'description' => 'Mon projet',
                'languages' => '["PHP","Symfony"]',
            ]),
            $entityManager,
            $uploadHandler
        );

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertSame(['error' => 'File is required'], $this->responseData($response));
    }

    public function testUploadRejectsNonAdmin(): void
    {
        $controller = $this->createController(false);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $uploadHandler = $this->createMock(\App\Service\ProjectUploadHandlerInterface::class);
        $entityManager->expects(self::never())->method('persist');
        $this->expectException(\Symfony\Component\Security\Core\Exception\AccessDeniedException::class);
        $controller->upload($this->createRequest(), $entityManager, $uploadHandler);
    }

    public function testRemoveProjectReturnsNotFound(): void
    {
        $controller = $this->createController();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(ProjectsRepository::class);
        $repository->expects(self::once())->method('find')->with('123')->willReturn(null);
        $entityManager->expects(self::never())->method('remove');

        $response = $controller->removeProject($this->createRequest(), $entityManager, $repository, '123');

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertSame(['error' => 'Inexistant project'], $this->responseData($response)
        );
    }

    public function testRemoveProjectDeletesExistingProject(): void
    {
        $controller = $this->createController();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(ProjectsRepository::class);
        $project = new Projects();
        $repository->expects(self::once())->method('find')->with('123')->willReturn($project);
        $entityManager->expects(self::once())->method('remove')->with($project);
        $entityManager->expects(self::once())->method('flush');
        $response = $controller->removeProject($this->createRequest(), $entityManager, $repository, '123');
        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testUpdateProjectRejectsMissingToken(): void
    {
        $controller = $this->createController();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $uploadHandler = $this->createMock(\App\Service\ProjectUploadHandlerInterface::class);
        $jwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $entityManager->expects(self::never())->method('flush');
        $this->expectException(UnauthorizedHttpException::class);
        $controller->updateProject($this->createRequest(), $entityManager, $uploadHandler, '123', $jwtManager);
    }

    public function testUpdateProjectReturnsNotFound(): void
    {
        $controller = $this->createController();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(ProjectsRepository::class);
        $entityManager->expects(self::once())->method('getRepository')->with(Projects::class)->willReturn($repository);
        $repository->expects(self::once())->method('find')->with('123')->willReturn(null);
        $entityManager->expects(self::never())->method('flush');
        $uploadHandler = $this->createMock(\App\Service\ProjectUploadHandlerInterface::class);
        $jwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $response = $controller->updateProject(
            $this->createRequest(
                [],
                [],
                ['HTTP_AUTHORIZATION' => 'Bearer test-token']
            ),
            $entityManager,
            $uploadHandler,
            '123',
            $jwtManager
        );

        self::assertSame(Response::HTTP_NOT_FOUND,$response->getStatusCode());
        self::assertSame(['error' => 'Project not found'],$this->responseData($response));
    }

    public function testUpdateProjectChangesNameAndDescription(): void
    {
        $controller = $this->createController();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $project = new Projects();
        $project->setName('Ancien nom');
        $project->setDescription('Ancienne description');
        $repository = $this->createMock(ProjectsRepository::class);
        $entityManager->expects(self::once())->method('getRepository')->with(Projects::class)->willReturn($repository);
        $repository->expects(self::once())->method('find')->with('123')->willReturn($project);
        $entityManager->expects(self::once())->method('flush');
        $uploadHandler = $this->createMock(\App\Service\ProjectUploadHandlerInterface::class);
        $jwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $response = $controller->updateProject(
            $this->createRequest(
                [
                    'name' => 'Nouveau nom',
                    'description' => 'Nouvelle description',
                ],
                [],
                ['HTTP_AUTHORIZATION' => 'Bearer test-token']
            ),
            $entityManager,
            $uploadHandler,
            '123',
            $jwtManager
        );

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame(['status' => 'Project updated successfully'], $this->responseData($response));
        self::assertSame('Nouveau nom', $project->getName());
        self::assertSame('Nouvelle description', $project->getDescription());
    }
}