<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Error;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Twig\Error\RuntimeError;

final class ExceptionListener
{
    private ?Throwable $lastThrowable = null;

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        // NotFoundHttpException during Twig rendering is converted to standard 404 page
        if ($throwable instanceof RuntimeError && $throwable->getPrevious() instanceof NotFoundHttpException) {
            $event->setThrowable($throwable->getPrevious());
        }

        $this->lastThrowable = $event->getThrowable();
    }

    public function getLastThrowable(): ?Throwable
    {
        return $this->lastThrowable;
    }
}
