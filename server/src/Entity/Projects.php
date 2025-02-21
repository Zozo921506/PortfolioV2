<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use App\Controller\ProjectsController;
use ApiPlatform\OpenApi\Model;
use ArrayObject;

#[ORM\Entity]
#[ApiResource(
    normalizationContext: ['groups' => ['project:read']],
    denormalizationContext: ['groups' => ['project:write']],
    inputFormats: ['multipart' => ['multipart/form-data']],
    operations:[
        new GetCollection(),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            uriTemplate: '/projects',
            controller: ProjectsController::class. '::upload',
            deserialize: false,
            openapi: new Model\Operation(
                requestBody: new Model\RequestBody(
                    content: new \ArrayObject(
                        [
                            'multipart/form-data' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'name' => [
                                            'type' => 'string'
                                        ],
                                        'description' => [
                                            'type' => 'string'
                                        ],
                                        'languages' => [
                                            'type' => 'array'
                                        ],
                                        'file' => [
                                            'type' => 'string', 
                                            'format' => 'binary'
                                        ]
                                    ] 
                                ]
                            ]
                        ]
                    )
                )
            )
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            uriTemplate: '/projects/{id}',
            controller: ProjectsController::class. '::updateProject',
            deserialize: false,
            openapi: new Model\Operation(
                requestBody: new Model\RequestBody(
                    content: new ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'name' => [
                                        'type' => 'string'
                                    ],
                                    'description' => [
                                        'type' => 'string'
                                    ],
                                    'languages' => [
                                        'type' => 'array'
                                    ],
                                    'file' => [
                                        'type' => 'string', 
                                        'format' => 'binary'
                                    ]
                                ] 
                            ]
                        ]
                    ])
                )
            )
        ),
        new Delete(security: "is_granted('ROLE_ADMIN')")
    ]
)]
#[Vich\Uploadable]
class Projects
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['project:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['project:read', 'project:write'])]
    private ?string $name = null;
    
    #[ORM\Column(length: 255)]
    #[Groups(['project:read', 'project:write'])]
    private ?string $imagePath = null;

    #[Vich\UploadableField(mapping: 'projects', fileNameProperty: 'imagePath')]
    #[Groups(['project:write'])]
    #[Assert\File(
        maxSize: '2M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        mimeTypesMessage: 'Veuillez télécharger une image valide (JPEG, PNG, WEBP)'
    )]
    private ?File $file = null;

    #[ORM\Column(length: 1000)]
    #[Groups(['project:read', 'project:write'])]
    private ?string $description = null;

    #[ORM\Column(type: 'array')]
    #[Groups(['project:read', 'project:write'])]
    private ?array $languages = [];

    #[ORM\Column(options: ["default" => "CURRENT_TIMESTAMP"])]
    #[SerializedName('created_at')]
    #[Groups(['project:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(options: ["default" => "CURRENT_TIMESTAMP"])]
    #[SerializedName('updated_at')]
    #[Groups(['project:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setImagePath(?string $imagePath): void
    {
        $this->imagePath = $imagePath;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): void
    {
        $this->file = $file;
        if ($file)
        {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setLanguages(?array $languages): void
    {
        $this->languages = $languages;
    }

    public function getLanguages(): ?array
    {
        return $this->languages;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    #[Groups(['project:read'])]
    public function getImageUrl(): ?string
    {
        return $this->imagePath ? '/uploads/images/' . $this->imagePath : null;
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
