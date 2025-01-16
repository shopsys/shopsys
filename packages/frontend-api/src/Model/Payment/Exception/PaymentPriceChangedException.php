<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Payment\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

class PaymentPriceChangedException extends Exception
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $currentPaymentPrice
     */
    public function __construct(protected PriceInterface $currentPaymentPrice)
    {
        parent::__construct();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    public function getCurrentPaymentPrice(): PriceInterface
    {
        return $this->currentPaymentPrice;
    }
}
