<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\Exception;

class RefundAmountNotPositiveException extends PaymentTransactionRefundException
{
    public function __construct()
    {
        parent::__construct('Refund amount should be positive.');
    }
}
