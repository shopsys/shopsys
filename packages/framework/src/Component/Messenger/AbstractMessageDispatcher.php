<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Messenger;

use Symfony\Component\Messenger\MessageBusInterface;

abstract class AbstractMessageDispatcher
{
    protected string $transportDsn;

    protected MessageBusInterface $messageBus;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Messenger\MessageDispatcherDependency $messageDispatcherDependency
     */
    public function __construct(
        MessageDispatcherDependency $messageDispatcherDependency,
    ) {
        $this->transportDsn = $messageDispatcherDependency->transportDsn;
        $this->messageBus = $messageDispatcherDependency->messageBus;

        if ($this->transportDsn === '') {
            $this->messageBus = new NullMessageBus();
        }
    }
}
