<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Transport\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class TransportPriceChangedException extends Exception
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $currentTransportPrice
     */
    public function __construct(protected Price $currentTransportPrice)
    {
        parent::__construct();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getCurrentTransportPrice(): Price
    {
        return $this->currentTransportPrice;
    }
}
