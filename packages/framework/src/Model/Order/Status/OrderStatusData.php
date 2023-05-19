<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status;

class OrderStatusData
{
    /**
     * @var string[]|null[]
     */
    public $name;

    public function __construct()
    {
        $this->name = [];
    }
}
