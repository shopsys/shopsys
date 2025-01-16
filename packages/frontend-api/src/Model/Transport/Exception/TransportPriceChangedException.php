<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Transport\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

class TransportPriceChangedException extends Exception
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $currentTransportPrice
     */
    public function __construct(protected PriceInterface $currentTransportPrice)
    {
        parent::__construct();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    public function getCurrentTransportPrice(): PriceInterface
    {
        return $this->currentTransportPrice;
    }
}
