<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Projects;
use App\Repository\ProjectsRepository;
use Vich\UploaderBundle\Handler\UploadHandler;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;


final class ProjectsController extends AbstractController
{
    #[Route('/api/projects', methods: ['POST'])]
    public function upload(Request $request, EntityManagerInterface $entity, UploadHandler $uploadHandler): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $project = new Projects();
        $project->setName($request->request->get('name'));
        $project->setDescription($request->request->get('description'));
        $project->setLanguages(json_decode($request->request->get('languages'), true));
        $file = $request->files->get('file');
        $file = $request->files->get('file');
        if (!$file) {
            return new JsonResponse(['error' => 'File is required'], Response::HTTP_BAD_REQUEST);
        }
        
        $project->setFile($file);
        $uploadHandler->upload($project, 'file');
        
        if (!$project->getCreatedAt())
        {
            $project->setCreatedAt(new \DateTimeImmutable('now'));
        }

        $project->setUpdatedAt(new \DateTimeImmutable('now'));
        $entity->persist($project);
        $entity->flush();
        return new JsonResponse(['status' => 'Projet ajouté'], Response::HTTP_CREATED);
    }

    #[Route('/api/projects/{id}', methods: ['DELETE'])]
    public function removeProject(Request $request, EntityManagerInterface $entity, ProjectsRepository $repository, $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $project = $repository->find($id);
        if (!$project)
        {
            return new JsonResponse(['error' => 'Inexistant project'], Response::HTTP_NOT_FOUND);
        }

        $entity->remove($project);
        $entity->flush();
        return new JsonResponse(['status' => 'Project deleted'], Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/projects/{id}', methods: ['PUT', 'POST'])]
    public function updateProject(Request $request, EntityManagerInterface $entity, UploadHandler $uploadHandler, $id, JWTTokenManagerInterface $jwtManager): Response
    {
        $token = $request->headers->get('Authorization');
        if (!$token) 
        {
            throw new UnauthorizedHttpException('Bearer', 'No token provided');
        }

        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $project = $entity->getRepository(Projects::class)->find($id);
        if (!$project)
        {
            return new JsonResponse(['error' => 'Project not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = $request->request->all();
        if (isset($data['name']))
        {
            $project->setName($data['name']);
        }
    
        if (isset($data['description']))
        {
            $project->setDescription($data['description']);
        }
    
        if (isset($data['languages']))
        {
            $languages = preg_replace('/\s+/', '', $data['languages']);
            $languages = preg_replace('/[^a-zA-Z0-9,\']/', '', $languages);
            $languages = explode(',', $languages);
            $project->setLanguages($languages);
        }
    
        $file = $request->files->get('file');
        if ($file)
        {
            if ($project->getImagePath())
            {
                $uploadHandler->remove($project, 'file');
            }
            
            $project->setFile($file);
            $uploadHandler->upload($project, 'file');
        }

        $project->setUpdatedAt(new \DateTimeImmutable());
        $entity->flush();

        return new JsonResponse(['status' => 'Project updated successfully'], Response::HTTP_OK);
    }

}
