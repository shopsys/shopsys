<?php

namespace Shopsys\FrameworkBundle\Component\Error;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ExceptionListener
{
    /**
     * @var \Exception|null
     */
    private $lastException;

    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $this->lastException = $event->getException();
    }

    public function getLastException(): ?\Exception
    {
        return $this->lastException;
    }
}
