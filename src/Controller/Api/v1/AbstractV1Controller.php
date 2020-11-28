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

    public function jsonSuccess(array $data): JsonResponse
    {
        return new JsonResponse($data, Response::HTTP_OK);
    }

    public function error(string $message): Response
    {
        return new Response($message, Response::HTTP_BAD_REQUEST);
    }

    public function jsonError(array $errors): JsonResponse
    {
        return new JsonResponse($errors, Response::HTTP_OK);
    }
}