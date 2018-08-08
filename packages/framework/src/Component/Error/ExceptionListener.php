<?php

namespace Shopsys\FrameworkBundle\Component\Error;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ExceptionListener
{
    /**
     * @var \Exception|null
     */
    private $lastException;

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
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
