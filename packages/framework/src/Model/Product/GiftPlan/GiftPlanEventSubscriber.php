<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GiftPlanEventSubscriber implements EventSubscriberInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftFlagSynchronizerFacade $giftFlagSynchronizerFacade
     */
    public function __construct(
        protected readonly GiftFlagSynchronizerFacade $giftFlagSynchronizerFacade,
    ) {
    }

    /**
     * @return array
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            GiftPlanEvent::CREATE => 'onChanged',
            GiftPlanEvent::UPDATE => 'onChanged',
            GiftPlanEvent::DELETE => 'onChanged',
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanEvent $event
     */
    public function onChanged(GiftPlanEvent $event): void
    {
        $this->giftFlagSynchronizerFacade->dispatchMainProductForGriftFlagRecalculation($event->getMainProducts());
    }
}
