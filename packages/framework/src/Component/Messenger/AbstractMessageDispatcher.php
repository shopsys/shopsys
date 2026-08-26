<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Messenger;

use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractMessageDispatcher
{
    protected MessageBusInterface $messageBus;

    /**
     * Set mandatory dependencies with setter injection to keep constructor clean for extending classes
     *
     * @internal This method is public only for the purpose of setter injection
     */
    #[Required]
    public function setMessageDispatcherDependency(MessageDispatcherDependency $messageDispatcherDependency): void
    {
        $this->messageBus = $messageDispatcherDependency->messageBus;
    }
}
