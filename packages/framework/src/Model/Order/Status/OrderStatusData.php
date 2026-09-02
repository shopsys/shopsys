<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status;

class OrderStatusData
{
    /**
     * @var string[]|null[]
     */
    public $name;

    /**
     * @var bool
     */
    public $productReviewsAllowed = false;

    public function __construct()
    {
        $this->name = [];
    }
}
