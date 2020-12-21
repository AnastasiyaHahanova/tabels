<?php

namespace App\Controller\Api\v1;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method User getUser()
 */
class AbstractV1Controller extends AbstractController
{
    public function success(string $message): Response
    {
        return new Response($message, Response::HTTP_OK);
    }

    public function errors(array $errors, string $title = 'Errors'): JsonResponse
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