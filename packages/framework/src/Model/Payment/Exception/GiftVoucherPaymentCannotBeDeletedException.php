<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Exception;

use Exception;

class GiftVoucherPaymentCannotBeDeletedException extends Exception
{
    public function __construct(
        string $message = 'Payment of type gift voucher cannot be deleted.',
        ?Exception $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
