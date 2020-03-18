<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Group;

use Symfony\Contracts\EventDispatcher\Event;

class PricingGroupEvent extends Event
{
    /**
     * The CREATE event occurs once a pricing group was created.
     *
     * This event allows you to run jobs dependent on the pricing group creation.
     */
    public const CREATE = 'pricingGroup.create';
    /**
     * The UPDATE event occurs once a pricing group was changed.
     *
     * This event allows you to run jobs dependent on the pricing group change.
     */
    public const UPDATE = 'pricingGroup.update';
    /**
     * The DELETE event occurs once a pricing group was deleted.
     *
     * This event allows you to run jobs dependent on the pricing group deletion.
     */
    public const DELETE = 'pricingGroup.delete';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    protected $pricingGroup;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     */
    public function __construct(PricingGroup $pricingGroup)
    {
        $this->pricingGroup = $pricingGroup;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    public function getPricingGroup(): PricingGroup
    {
        return $this->pricingGroup;
    }
}
