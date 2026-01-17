<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Payment;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Payment\Payment;

class CartPaymentData
{
    public Payment $payment;

    public Money $watchedPrice;

    public ?string $goPayBankSwift;
}
