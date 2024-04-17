<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Messenger;

use Override;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * The decorator prevents errors when transport DSN is empty
 */
class MessageBusDecorator implements MessageBusInterface
{
    /**
     * @param string $transportDsn
     * @param \Symfony\Component\Messenger\MessageBusInterface $messageBus
     */
    public function __construct(
        protected readonly string $transportDsn,
        protected readonly MessageBusInterface $messageBus,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function dispatch(object $message, array $stamps = []): Envelope
    {
        if ($this->transportDsn === '') {
            return new Envelope($message, $stamps);
        }

        return $this->messageBus->dispatch($message, $stamps);
    }
}
