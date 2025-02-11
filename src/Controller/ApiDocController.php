<?php

namespace App\Controller;

use Nelmio\ApiDocBundle\Controller\SwaggerUiController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ApiDocController
{
    private SwaggerUiController $swaggerUiController;

    public function __construct(SwaggerUiController $swaggerUiController)
    {
        $this->swaggerUiController = $swaggerUiController;
    }

    #[Route('/api/doc', name: 'api_doc', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        return $this->swaggerUiController->__invoke($request);
    }
}