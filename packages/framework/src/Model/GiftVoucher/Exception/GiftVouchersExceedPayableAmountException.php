<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GiftVoucher\Exception;

use Exception;

class GiftVouchersExceedPayableAmountException extends GiftVoucherException
{
    public function __construct(?Exception $previous = null)
    {
        parent::__construct('Applied gift vouchers exceed the payable amount of the order.', 0, $previous);
    }
}
