<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Payment\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

class PaymentPriceChangedException extends Exception
{
    public function __construct(protected PriceInterface $currentPaymentPrice)
    {
        parent::__construct();
    }

    public function getCurrentPaymentPrice(): PriceInterface
    {
        return $this->currentPaymentPrice;
    }
}
