<?php

namespace Shopsys\FrameworkBundle\Component\Error;

use Symfony\Component\HttpKernel\EventListener\ExceptionListener;
use Psr\Log\LoggerInterface;

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
    public function __construct($controller, ?LoggerInterface $logger = null, bool $debug = false, ErrorIdProvider $errorIdProvider = null)
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
            $message .= sprintf(' Error ID: %s', $this->errorIdProvider->getErrorId());
            parent::logException($exception, $message);
        }
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Component\Error\ErrorIdProvider $errorIdProvider
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setErrorIdProvider(ErrorIdProvider $errorIdProvider): void
    {
        if ($this->errorIdProvider && $this->errorIdProvider !== $errorIdProvider) {
            throw new \BadMethodCallException(
                sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__)
            );
        }
        if (!$this->errorIdProvider) {
            @trigger_error(
                sprintf(
                    'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                    __METHOD__
                ),
                E_USER_DEPRECATED
            );
            $this->errorIdProvider = $errorIdProvider;
        }
    }
}
