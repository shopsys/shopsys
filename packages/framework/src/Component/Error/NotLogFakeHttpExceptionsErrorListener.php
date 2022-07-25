<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Error;

use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Error\Exception\FakeHttpException;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Throwable;

class NotLogFakeHttpExceptionsErrorListener extends ErrorListener
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
    public function __construct(
        $controller,
        ErrorIdProvider $errorIdProvider,
        ?LoggerInterface $logger = null,
        bool $debug = false
    ) {
        parent::__construct($controller, $logger, $debug);

        $this->errorIdProvider = $errorIdProvider;
    }

    /**
     * @inheritDoc
     */
    protected function logException(Throwable $exception, string $message, ?string $logLevel = null): void
    {
        if (!$exception instanceof FakeHttpException) {
            $message .= sprintf(' Error ID: %s', $this->errorIdProvider->getErrorId());

            parent::logException($exception, $message, $logLevel);
        }
    }
}
