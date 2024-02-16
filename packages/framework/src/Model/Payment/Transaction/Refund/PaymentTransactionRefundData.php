<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund;

class PaymentTransactionRefundData
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public $refundAmount;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public $refundedAmount;

    /**
     * @var bool
     */
    public $executeRefund;

    public function __construct()
    {
        $this->executeRefund = false;
    }
}
