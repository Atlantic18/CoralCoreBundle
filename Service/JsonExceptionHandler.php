<?php

namespace Coral\CoreBundle\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Psr\Log\LoggerInterface;

use Coral\CoreBundle\Exception\JsonException;
use Coral\CoreBundle\Exception\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class JsonExceptionHandler
{
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception  = $event->getException();
        $logMessage = 'Exception [' . get_class($exception) . ']: ' . $exception->getMessage();

        if($exception instanceof AuthenticationException) {
            $this->logger->error($logMessage);

            $event->setResponse(new JsonResponse(array(
                'status'    => 'failed',
                'message'   => $exception->getMessage(),
                'timestamp' => time()
            ), 401));
        }
        elseif($exception instanceof NotFoundHttpException) {
            $this->logger->error($logMessage);

            $event->setResponse(new JsonResponse(array(
                'status'  => 'failed',
                'message' => $exception->getMessage()
            ), 404));
        }
        elseif($exception instanceof JsonException) {
            $this->logger->critical($logMessage);

            $event->setResponse(new JsonResponse(array(
                'status'  => 'failed',
                'message' => 'Json error: ' . $exception->getMessage()
            ), 500));
        }
        elseif($event->getRequest()->headers->has('X-CORAL-ACCOUNT') || $event->getRequest()->isXmlHttpRequest()) {
            $this->logger->critical($logMessage);

            //show only for JSON requests
            $event->setResponse(new JsonResponse(array(
                'status'  => 'failed',
                'message' => 'Internal error [' . get_class($exception) . ']: ' . $exception->getMessage()
            ), 500));
        }
    }
}
