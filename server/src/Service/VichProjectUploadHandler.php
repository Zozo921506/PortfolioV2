<?php

namespace App\Service;

use App\Entity\Projects;
use Vich\UploaderBundle\Handler\UploadHandler;

final class VichProjectUploadHandler implements ProjectUploadHandlerInterface
{
    public function __construct(
        private UploadHandler $uploadHandler
    ) {
    }

    public function upload(Projects $project, string $fieldName): void
    {
        $this->uploadHandler->upload($project, $fieldName);
    }

    public function remove(Projects $project, string $fieldName): void
    {
        $this->uploadHandler->remove($project, $fieldName);
    }
}