<?php

namespace App\Controller\Api\v1;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AbstractV1Controller extends AbstractController
{
    public function success(string $message): Response
    {
        return new Response($message, Response::HTTP_OK);
    }

    public function jsonData(array $data): JsonResponse
    {
        return new JsonResponse($data, Response::HTTP_OK);
    }

    public function errors(array $errors, $title = 'Errors'): JsonResponse
    {
        return new JsonResponse([
            'title'          => $title,
            'invalid-params' => $errors
        ], Response::HTTP_BAD_REQUEST);
    }

    public function error(string $message, string $title = 'Error'): JsonResponse
    {
        return new JsonResponse([
            'title'  => $title,
            'detail' => $message
        ], Response::HTTP_BAD_REQUEST);
    }
}