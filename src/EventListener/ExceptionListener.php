<?php

namespace App\EventListener;

use FOS\RestBundle\Exception\InvalidParameterException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionListener
{
    public const REQUIREMENTS = [
        '^[1-9]\d*$'          => 'positive integer',
        '^[0-9][0-9]?$|^100$' => 'from 0 to 100',
        '(row|column)'        => 'Please specify as value ( row|column )'
    ];

    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelException(ExceptionEvent $event)
    {

        $exception = $event->getThrowable();
        $response  = new Response();
        switch (true) {
            case $exception instanceof InvalidParameterException:
                $message = $this->replaceRequirements($exception->getMessage());
                $content = json_encode([
                    'title'  => 'Invalid parameters',
                    'detail' => $message
                ]);
                $response->setContent($content);
                $response->headers->replace(['Content-Type' => 'application/json']);
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
                break;
            case $exception instanceof NotFoundHttpException:
                $message = $this->replaceRequirements($exception->getMessage());
                $content = json_encode([
                    'title'  => 'Not found',
                    'detail' => $message
                ]);
                $response->setContent($content);
                $response->headers->replace(['Content-Type' => 'application/json']);
                $response->setStatusCode(Response::HTTP_NOT_FOUND);
                break;
            default :
                $content = json_encode([
                    'title'  => 'Error',
                    'detail' => 'Internal server error'
                ]);
                $response->setContent($content);
                $response->headers->replace(['Content-Type' => 'application/json']);
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
                $this->logger->error($exception);
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