<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Error;

use Monolog\LogRecord;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ResetInterface;

final class ErrorIdLogProcessor implements EventSubscriberInterface, ResetInterface
{
    private ?string $errorId = null;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Error\ErrorIdProvider $errorIdProvider
     */
    public function __construct(
        private readonly ErrorIdProvider $errorIdProvider,
    ) {
    }

    /**
     * @param \Monolog\LogRecord $record
     * @return \Monolog\LogRecord
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        if ($this->errorId !== null) {
            $record['extra']['errorId'] = $this->errorId;
        }

        return $record;
    }

    public function onException(): void
    {
        $this->errorId = $this->errorIdProvider->getErrorId();
    }

    public function reset(): void
    {
        $this->errorId = null;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onException', 1],
        ];
    }
}
