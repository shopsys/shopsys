<?php

namespace Shopsys\FrameworkBundle\Component\Error;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Error\RuntimeError;

class ExceptionListener
{
    /**
     * @var \Exception|null
     */
    protected $lastException;

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $throwable = $event->getThrowable();

        // NotFoundHttpException during Twig rendering is converted to standard 404 page
        if ($throwable instanceof RuntimeError && $throwable->getPrevious() instanceof NotFoundHttpException) {
            $event->setThrowable($throwable->getPrevious());
        }

        $this->lastException = $event->getException();
    }

    /**
     * @return \Exception|null
     */
    public function getLastException()
    {
        return $this->lastException;
    }
}
