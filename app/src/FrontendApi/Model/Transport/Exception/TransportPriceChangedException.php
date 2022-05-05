<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Transport\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class TransportPriceChangedException extends Exception
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private Price $currentTransportPrice;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $currentTransportPrice
     */
    public function __construct(Price $currentTransportPrice)
    {
        parent::__construct();

        $this->currentTransportPrice = $currentTransportPrice;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getCurrentTransportPrice(): Price
    {
        return $this->currentTransportPrice;
    }
}
