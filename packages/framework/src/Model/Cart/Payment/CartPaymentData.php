<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Payment;

use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

class CartPaymentData
{
    public Payment $payment;

    public PriceInterface $watchedPrice;

    public ?string $goPayBankSwift;
}
