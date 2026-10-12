<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class ProjectsControllerTest extends WebTestCase
{
    public function testProjectsPostRejectsAnonymousRequest(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/projects',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Projet de test',
                'description' => 'Description fonctionnelle',
                'languages' => '["PHP","Symfony"]',
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCreateProjectRejectsAnonymousRequest(): void 
    { 
        $client = static::createClient(); 
        $client->request('POST', '/api/projects'); 
        $this->assertResponseStatusCodeSame(401); 
    }
        
    public function testUpdateProjectRejectsAnonymousRequest(): void 
    { 
        $client = static::createClient(); 
        $client->request('PUT', '/api/projects/1'); 
        $this->assertResponseStatusCodeSame(401); 
    }
    
    public function testDeleteProjectRejectsAnonymousRequest(): void 
    { 
        $client = static::createClient(); 
        $client->request('DELETE', '/api/projects/1'); 
        $this->assertResponseStatusCodeSame(401); 
    }

    public function testRegularUserCannotCreateProject(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()
            ->get(EntityManagerInterface::class);

        $user = (new User())
            ->setEmail('functional-user-' . bin2hex(random_bytes(4)) . '@example.com')
            ->setPassword('test-password-hash')
            ->setRoles(['ROLE_USER'])
            ->setFirstname('Test')
            ->setLastname('User');

        $entityManager->persist($user);
        $entityManager->flush();

        try {
            /** @var JWTTokenManagerInterface $jwtManager */
            $jwtManager = $client->getContainer()
                ->get(JWTTokenManagerInterface::class);

            $jwt = $jwtManager->create($user);

            $client->request(
                'POST',
                '/api/projects',
                [],
                [],
                [
                    'HTTP_AUTHORIZATION' => 'Bearer ' . $jwt,
                ],
            );

            $this->assertResponseStatusCodeSame(403);
        } finally {
            $entityManager->remove($user);
            $entityManager->flush();
        }
    }

    public function testAdminCanCreateProject(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $jwtManager = $container->get(\Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface::class);
        $uploadHandler = $container->get(\App\Service\ProjectUploadHandlerInterface::class);
        $projectName = 'Functional test ' . uniqid();
        $email = 'admin-' . uniqid() . '@example.test';
        $admin = new \App\Entity\User();
        $admin->setEmail($email);
        $admin->setPassword('test-password-hash');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setFirstname('Test');
        $admin->setLastname('Admin');
        $entityManager->persist($admin);
        $entityManager->flush();
        $jwt = $jwtManager->create($admin);
        $imagePath = tempnam(sys_get_temp_dir(), 'project_test_');
        file_put_contents(
            $imagePath,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j7Z8AAAAASUVORK5CYII=')
        );

        $uploadedFile = new \Symfony\Component\HttpFoundation\File\UploadedFile(
            $imagePath,
            'test.png',
            'image/png',
            null,
            true
        );

        try {
            $client->request(
                'POST',
                '/api/projects',
                [
                    'name' => $projectName,
                    'description' => 'Description de test',
                    'languages' => json_encode(['PHP', 'Symfony']),
                ],
                ['file' => $uploadedFile],
                ['HTTP_AUTHORIZATION' => 'Bearer ' . $jwt]
            );

            self::assertResponseStatusCodeSame(201);
            $project = $entityManager->getRepository(\App\Entity\Projects::class)->findOneBy(['name' => $projectName]);
            self::assertNotNull($project);
            self::assertSame('Description de test', $project->getDescription());
        } finally {
            $project = $entityManager->getRepository(\App\Entity\Projects::class)->findOneBy(['name' => $projectName]);

            if ($project !== null) {
                $uploadHandler->remove($project, 'file');
                $entityManager->remove($project);
            }

            $entityManager->remove($admin);
            $entityManager->flush();

            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
    }
}