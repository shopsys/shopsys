<?php

declare(strict_types=1);

namespace App\Model\Cart\Payment;

use App\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Component\Money\Money;

class CartPaymentData
{
    /**
     * @var \App\Model\Payment\Payment
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
