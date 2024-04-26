<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Payment;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Payment\Payment;

class CartPaymentData
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    public Payment $payment;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public Money $watchedPrice;

    /**
     * @var string|null
     */
    public ?string $goPayBankSwift;
}
