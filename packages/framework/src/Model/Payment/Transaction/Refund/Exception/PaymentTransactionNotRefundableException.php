<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\Exception;

class PaymentTransactionNotRefundableException extends PaymentTransactionRefundException
{
    public function __construct()
    {
        parent::__construct('Payment transaction is not refundable.');
    }
}
