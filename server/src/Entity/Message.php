<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use App\Controller\MessageController;
use ApiPlatform\OpenApi\Model;
use ArrayObject;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['Message:read']],
    operations:[
        new GetCollection(
            uriTemplate: '/getProjectMessage/{id}',
            controller: MessageController::class . '::getProjectMessage',
            read: false,
            normalizationContext: ['groups' => ['Message:read']],
        ),
        new Post(
            uriTemplate: '/new_message',
            controller: MessageController::class. '::newMessage',
            deserialize: false,
            openapi: new Model\Operation(
                requestBody: new Model\RequestBody(
                    content: new \ArrayObject(
                        [
                            'multipart/form-data' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'idProject' => [
                                            'type' => 'integer'
                                        ],
                                        'content' => [
                                            'type' => 'string'
                                        ]
                                    ] 
                                ]
                            ]
                        ]
                    )
                )
            )
        ),
        // new Post(
        //     security: "is_granted('ROLE_ADMIN')",
        //     uriTemplate: '/projects/{id}',
        //     controller: ProjectsController::class. '::updateProject',
        //     deserialize: false,
        //     openapi: new Model\Operation(
        //         requestBody: new Model\RequestBody(
        //             content: new ArrayObject([
        //                 'multipart/form-data' => [
        //                     'schema' => [
        //                         'type' => 'object',
        //                         'properties' => [
        //                             'name' => [
        //                                 'type' => 'string'
        //                             ],
        //                             'description' => [
        //                                 'type' => 'string'
        //                             ],
        //                             'languages' => [
        //                                 'type' => 'array'
        //                             ],
        //                             'file' => [
        //                                 'type' => 'string', 
        //                                 'format' => 'binary'
        //                             ]
        //                         ] 
        //                     ]
        //                 ]
        //             ])
        //         )
        //     )
        // ),
        new Delete(
            uriTemplate: '/message/{id}',
            security: "is_granted('ROLE_ADMIN')"
        )
    ]
)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['Message:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['Message:read'])]
    private ?int $id_project = null;

    #[ORM\Column(length: 255)]
    #[Groups(['Message:read'])]
    private ?string $text = null;

    #[ORM\Column]
    #[Groups(['Message:read'])]
    private ?int $id_user = null;

    #[ORM\Column]
    #[Groups(['Message:read'])]
    private ?\DateTimeImmutable $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getIdProject(): ?int
    {
        return $this->id_project;
    }

    public function setIdProject(int $id_project): static
    {
        $this->id_project = $id_project;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getIdUser(): ?int
    {
        return $this->id_user;
    }

    public function setIdUser(int $id_user): static
    {
        $this->id_user = $id_user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}
