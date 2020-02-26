<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Error;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;

class NotLogFakeHttpExceptionsExceptionListener extends ErrorListener
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Error\ErrorIdProvider|null
     */
    protected $errorIdProvider;

    /**
     * @param mixed $controller
     * @param \Psr\Log\LoggerInterface|null $logger
     * @param bool $debug
     * @param \Shopsys\FrameworkBundle\Component\Error\ErrorIdProvider|null $errorIdProvider
     */
    public function __construct($controller, ?LoggerInterface $logger = null, bool $debug = false, ?ErrorIdProvider $errorIdProvider = null)
    {
        parent::__construct($controller, $logger, $debug);
        $this->errorIdProvider = $errorIdProvider;
    }

    /**
     * @inheritDoc
     */
    protected function logException(\Throwable $exception, $message): void
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
