<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Messenger;

use Symfony\Component\Messenger\MessageBusInterface;

class MessageDispatcherDependency
{
    public function __construct(
        public readonly MessageBusInterface $messageBus,
    ) {
    }
}
