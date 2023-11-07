<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * This Message Bus is used when transport DSN is empty to prevent errors
 *
 * @internal
 */
class NullMessageBus implements MessageBusInterface
{
    /**
     * Dispatch method only wraps the message in Envelope, but does not really dispatch it
     * For the current implementation see the implementation of MessageBusInterface in Symfony Messenger
     *
     * {@inheritdoc}
     */
    public function dispatch(object $message, array $stamps = []): Envelope
    {
        return new Envelope($message, $stamps);
    }
}
