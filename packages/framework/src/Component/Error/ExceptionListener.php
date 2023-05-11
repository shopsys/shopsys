<?php

namespace Shopsys\FrameworkBundle\Component\Error;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Twig\Error\RuntimeError;

class ExceptionListener
{
    protected ?Throwable $lastThrowable;

    /**
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        // NotFoundHttpException during Twig rendering is converted to standard 404 page
        if ($throwable instanceof RuntimeError && $throwable->getPrevious() instanceof NotFoundHttpException) {
            $event->setThrowable($throwable->getPrevious());
        }

        $this->lastThrowable = $event->getThrowable();
    }

    /**
     * @return \Throwable|null
     */
    public function getLastThrowable(): ?Throwable
    {
        return $this->lastThrowable;
    }
}
