<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\Exception;

class RefundAmountGreaterThanRefundableAmountException extends PaymentTransactionRefundException
{
    public function __construct()
    {
        parent::__construct('Refundable amount should be greater than or equal to refunded amount.');
    }
}
