<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Messenger;

class ExampleDispatcher extends AbstractMessageDispatcher
{
    /**
     * @param string $value
     */
    public function dispatchExampleMessage(string $value): void
    {
        $this->messageBus->dispatch(new ExampleMessage($value));
    }
}
