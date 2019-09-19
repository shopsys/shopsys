<?php

namespace Shopsys\FrameworkBundle\Component\Error;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener;

class NotLogFakeHttpExceptionsExceptionListener extends ExceptionListener
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Error\ErrorIdProvider
     */
    protected $errorIdProvider;

    /**
     * @param mixed $controller
     * @param \Shopsys\FrameworkBundle\Component\Error\ErrorIdProvider $errorIdProvider
     * @param \Psr\Log\LoggerInterface|null $logger
     * @param bool $debug
     */
    public function __construct($controller, ErrorIdProvider $errorIdProvider, ?LoggerInterface $logger = null, bool $debug = false)
    {
        parent::__construct($controller, $logger, $debug);
        $this->errorIdProvider = $errorIdProvider;
    }

    /**
     * @inheritDoc
     */
    protected function logException(\Exception $exception, $message)
    {
        if (!$exception instanceof \Shopsys\FrameworkBundle\Component\Error\Exception\FakeHttpException) {
            $message .= sprintf('Error ID: %s', $this->errorIdProvider->getErrorId());
            parent::logException($exception, $message);
        }
    }
}
