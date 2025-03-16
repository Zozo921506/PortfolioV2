<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DebugController
{
    #[Route('/debug-env', name: 'debug_env')]
    public function debugEnv(): JsonResponse
    {
        return new JsonResponse($_ENV);
    }
}
