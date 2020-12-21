<?php

namespace App\EventListener;

use FOS\RestBundle\Exception\InvalidParameterException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public const REQUIREMENTS = [
        '^[1-9]\d*$' => 'positive integer'
    ];

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $response  = new Response();
        switch (true) {
            case $exception instanceof InvalidParameterException:
                $message = $this->replaceRequirements($exception->getMessage());
                $response->setContent($message);
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
                break;
        }

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $event->setResponse($response);
    }

    public function replaceRequirements(string $message): string
    {
        foreach (self::REQUIREMENTS as $requirement => $replace) {
            if (strpos($message, $requirement)) {
                return str_replace($requirement, $replace, $message);
            }
        }

        return $message;
    }
}