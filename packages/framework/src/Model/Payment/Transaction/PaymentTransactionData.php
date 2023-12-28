<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Transaction;

class PaymentTransactionData
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    public $order;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment|null
     */
    public $payment;

    /**
     * @var string|null
     */
    public $externalPaymentIdentifier;

    /**
     * @var string|null
     */
    public $externalPaymentStatus;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public $paidAmount;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public $refundedAmount;
}
