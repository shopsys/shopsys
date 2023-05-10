<?php

declare(strict_types=1);

namespace App\Model\Payment\Transaction;

use App\Model\Order\Order;
use App\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Component\Money\Money;

class PaymentTransactionData
{
    /**
     * @var \App\Model\Order\Order|null
     */
    public ?Order $order = null;

    /**
     * @var \App\Model\Payment\Payment|null
     */
    public ?Payment $payment = null;

    /**
     * @var string|null
     */
    public ?string $externalPaymentIdentifier = null;

    /**
     * @var string|null
     */
    public ?string $externalPaymentStatus = null;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public ?Money $paidAmount = null;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public ?Money $refundedAmount = null;
}
