<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Messenger;

use Symfony\Component\Messenger\MessageBusInterface;

class MessageDispatcherDependency
{
    /**
     * @param string $transportDsn
     * @param \Symfony\Component\Messenger\MessageBusInterface $messageBus
     */
    public function __construct(
        public readonly string $transportDsn,
        public readonly MessageBusInterface $messageBus,
    ) {
    }
}
