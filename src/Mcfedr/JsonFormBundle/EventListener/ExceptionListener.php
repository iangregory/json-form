<?php

declare(strict_types=1);

namespace Mcfedr\JsonFormBundle\EventListener;

use Mcfedr\JsonFormBundle\Exception\JsonHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ExceptionListener
{
    public function onKernelException($event): void
    {
        if ($event instanceof ExceptionEvent && method_exists($event, 'getThrowable')) {
            $exception = $event->getThrowable();
        } elseif ($event instanceof GetResponseForExceptionEvent) {
            $exception = $event->getException();
        } else {
            throw new InvalidArgumentException('onKernelException function only accepts GetResponseForExceptionEvent and ExceptionEvent arguments');
        }

        if ($exception instanceof JsonHttpException) {
            $errorData = [
                'error' => [
                    'code' => $exception->getStatusCode(),
                    'message' => $exception->getMessage(),
                ],
            ];
            if (($data = $exception->getData())) {
                $errorData['error']['info'] = $data;
            }
            $response = new JsonResponse($errorData);
            $event->setResponse($response);
        }
    }
}
