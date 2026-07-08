<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\Exception;

class RefundedAmountNegativeException extends PaymentTransactionRefundException
{
    public function __construct()
    {
        parent::__construct('Refunded amount should not be negative.');
    }
}
