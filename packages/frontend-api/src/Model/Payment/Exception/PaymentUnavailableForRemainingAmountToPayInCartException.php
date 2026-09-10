<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Payment\Exception;

use Exception;

class PaymentUnavailableForRemainingAmountToPayInCartException extends Exception
{
    public function __construct(
        string $message = 'Payment of type gift voucher is available only when the remaining amount to pay is zero, other payments only when it is not.',
        ?Exception $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
