<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Error;

use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Log\TracyFileLogger;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener;

class NotLogFakeHttpExceptionsExceptionListener extends ExceptionListener
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Error\ErrorIdProvider|null
     */
    protected $errorIdProvider;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Log\TracyFileLogger|null
     */
    protected $tracyFileLogger;

    /**
     * @param mixed $controller
     * @param \Psr\Log\LoggerInterface|null $logger
     * @param bool $debug
     * @param \Shopsys\FrameworkBundle\Component\Error\ErrorIdProvider|null $errorIdProvider
     * @param \Shopsys\FrameworkBundle\Component\Log\TracyFileLogger|null $tracyFileLogger
     */
    public function __construct($controller, ?LoggerInterface $logger = null, bool $debug = false, ?ErrorIdProvider $errorIdProvider = null, ?TracyFileLogger $tracyFileLogger = null)
    {
        parent::__construct($controller, $logger, $debug);
        $this->errorIdProvider = $errorIdProvider;
        $this->tracyFileLogger = $tracyFileLogger;
    }

    /**
     * @inheritDoc
     */
    protected function logException(\Exception $exception, $message)
    {
        if (!$exception instanceof \Shopsys\FrameworkBundle\Component\Error\Exception\FakeHttpException) {
            $this->errorIdProvider->setErrorId($exception);
            $this->tracyFileLogger->logToFile($exception);
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

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Component\Log\TracyFileLogger $tracyFileLogger
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setTracyFileLogger(TracyFileLogger $tracyFileLogger): void
    {
        if ($this->tracyFileLogger && $this->tracyFileLogger !== $tracyFileLogger) {
            throw new \BadMethodCallException(
                sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__)
            );
        }
        if (!$this->tracyFileLogger) {
            @trigger_error(
                sprintf(
                    'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                    __METHOD__
                ),
                E_USER_DEPRECATED
            );
            $this->tracyFileLogger = $tracyFileLogger;
        }
    }
}
