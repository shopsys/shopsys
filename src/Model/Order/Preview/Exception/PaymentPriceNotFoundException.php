<?php

declare(strict_types=1);

namespace App\Model\Order\Preview\Exception;

use App\Model\Payment\Payment;
use Exception;
use Throwable;

class PaymentPriceNotFoundException extends Exception
{
    /**
     * @param \App\Model\Payment\Payment $payment
     * @param \Throwable|null $previous
     */
    public function __construct(Payment $payment, ?Throwable $previous = null)
    {
        $message = sprintf('Price for Payment (ID=`%s`) was not found.', $payment->getId());
        parent::__construct($message, 0, $previous);
    }
}
