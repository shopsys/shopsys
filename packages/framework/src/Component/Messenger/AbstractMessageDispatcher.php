<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Messenger;

use Symfony\Component\Messenger\MessageBusInterface;

abstract class AbstractMessageDispatcher
{
    protected string $transportDsn;

    protected MessageBusInterface $messageBus;

    /**
     * Set mandatory dependencies with setter injection to keep constructor clean for extending classes
     *
     * @param \Shopsys\FrameworkBundle\Component\Messenger\MessageDispatcherDependency $messageDispatcherDependency
     * @internal This method is public only for the purpose of setter injection
     * @required
     */
    public function setMessageDispatcherDependency(MessageDispatcherDependency $messageDispatcherDependency): void
    {
        $this->transportDsn = $messageDispatcherDependency->transportDsn;
        $this->messageBus = $messageDispatcherDependency->messageBus;

        if ($this->transportDsn === '') {
            $this->messageBus = new NullMessageBus();
        }
    }
}
