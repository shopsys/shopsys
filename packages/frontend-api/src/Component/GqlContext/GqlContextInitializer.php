<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\GqlContext;

use Overblog\GraphQLBundle\Event\Events;
use Overblog\GraphQLBundle\Event\ExecutorArgumentsEvent;
use Override;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GqlContextInitializer implements EventSubscriberInterface
{
    /**
     * @return string[][][]
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            Events::PRE_EXECUTOR => [
                ['initializeContext'],
            ],
        ];
    }

    public function initializeContext(ExecutorArgumentsEvent $event): void
    {
        $flattened = new RecursiveIteratorIterator(new RecursiveArrayIterator((array)($event->getVariableValue() ?? []), RecursiveArrayIterator::CHILD_ARRAYS_ONLY));

        $event->getContextValue()['args'] = iterator_to_array($flattened, true);
    }
}
