<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

class GiftPlanData
{
    /**
     * @var string|null
     */
    public $uuid;

    /**
     * @var int|null
     */
    public $domainId;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public $mainProducts;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product|null
     */
    public $giftProduct;

    /**
     * @var \DateTimeImmutable|null
     */
    public $validFrom;

    /**
     * @var \DateTimeImmutable|null
     */
    public $validTo;

    public function __construct()
    {
        $this->mainProducts = [];
    }
}
