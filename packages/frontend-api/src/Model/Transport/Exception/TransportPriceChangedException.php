<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Transport\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

class TransportPriceChangedException extends Exception
{
    public function __construct(protected PriceInterface $currentTransportPrice)
    {
        parent::__construct();
    }

    public function getCurrentTransportPrice(): PriceInterface
    {
        return $this->currentTransportPrice;
    }
}
