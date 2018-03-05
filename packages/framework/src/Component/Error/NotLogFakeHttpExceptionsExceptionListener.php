<?php

namespace Shopsys\FrameworkBundle\Component\Error;

use Symfony\Component\HttpKernel\EventListener\ExceptionListener;

class NotLogFakeHttpExceptionsExceptionListener extends ExceptionListener
{
    /**
     * @inheritDoc
     */
    protected function logException(\Exception $exception, $message)
    {
        if (!$exception instanceof \Shopsys\FrameworkBundle\Component\Error\Exception\FakeHttpException) {
            parent::logException($exception, $message);
        }
    }
}
