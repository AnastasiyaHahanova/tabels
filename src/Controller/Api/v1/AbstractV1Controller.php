<?php

namespace App\Controller\Api\v1;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

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

    public function getErrorsMessageFromViolations(ConstraintViolationListInterface $list): array
    {
        $errorMessages = [];
        $i             = 0;
        while ($i < $list->count()) {
            $errorMessages[] = $list->get($i)->getMessage();
            $i++;
        }

        return $errorMessages;
    }
}