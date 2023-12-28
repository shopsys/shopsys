<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Transaction\Exception;

use Exception;

class PaymentTransactionHasNoAssignedPayment extends Exception
{
    public function __construct()
    {
        parent::__construct('Payment transaction has no assigned payment.');
    }
}
