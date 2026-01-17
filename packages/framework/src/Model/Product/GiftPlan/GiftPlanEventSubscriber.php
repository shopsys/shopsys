<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GiftPlanEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly GiftFlagSynchronizerFacade $giftFlagSynchronizerFacade,
    ) {
    }

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            GiftPlanEvent::CREATE => 'onChanged',
            GiftPlanEvent::UPDATE => 'onChanged',
            GiftPlanEvent::DELETE => 'onChanged',
        ];
    }

    public function onChanged(GiftPlanEvent $event): void
    {
        $this->giftFlagSynchronizerFacade->dispatchMainProductForGriftFlagRecalculation($event->getMainProducts());
    }
}
