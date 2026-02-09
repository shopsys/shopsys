<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Symfony\Contracts\EventDispatcher\Event;

class GiftPlanEvent extends Event
{
    public const string CREATE = 'giftPlan.create';
    public const string UPDATE = 'giftPlan.update';
    public const string DELETE = 'giftPlan.delete';

    public function __construct(
        protected readonly array $mainProducts,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getMainProducts(): array
    {
        return $this->mainProducts;
    }
}
