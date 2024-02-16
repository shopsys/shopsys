<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\GqlContext;

use Overblog\GraphQLBundle\Event\Events;
use Overblog\GraphQLBundle\Event\ExecutorArgumentsEvent;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GqlContextInitializer implements EventSubscriberInterface
{
    /**
     * @return string[][][]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Events::PRE_EXECUTOR => [
                ['initializeContext'],
            ],
        ];
    }

    /**
     * @param \Overblog\GraphQLBundle\Event\ExecutorArgumentsEvent $event
     */
    public function initializeContext(ExecutorArgumentsEvent $event): void
    {
        $flattened = new RecursiveIteratorIterator(new RecursiveArrayIterator($event->getVariableValue() ?? []));

        $event->getContextValue()['args'] = iterator_to_array($flattened, true);
    }
}
