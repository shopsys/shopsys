<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\Exception;

class RefundedAmountGreaterThanPaidAmountException extends PaymentTransactionRefundException
{
    public function __construct()
    {
        parent::__construct('Paid amount should be greater than or equal to refunded amount.');
    }
}
