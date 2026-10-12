<?php

namespace App\Service;

use App\Entity\Projects;

interface ProjectUploadHandlerInterface
{
    public function upload(Projects $project, string $fieldName): void;

    public function remove(Projects $project, string $fieldName): void;
}