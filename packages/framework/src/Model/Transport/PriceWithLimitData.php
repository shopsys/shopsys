<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

class PriceWithLimitData
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public $price;

    /**
     * @var int|null
     */
    public $maxWeight;

    /**
     * @var int|null
     */
    public $transportPriceId;
}
