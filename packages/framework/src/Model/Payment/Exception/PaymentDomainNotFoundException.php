<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Exception;

use Exception;

class PaymentDomainNotFoundException extends Exception
{
    public function __construct(int $domainId, ?int $paymentId = null, ?Exception $previous = null)
    {
        $paymentDescription = $paymentId !== null ? sprintf('with ID %d', $paymentId) : 'without ID';
        $message = sprintf('PaymentDomain for payment %s and domain ID %d not found.', $paymentDescription, $domainId);

        parent::__construct($message, 0, $previous);
    }
}
