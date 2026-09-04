<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GiftVoucher\Exception;

use Exception;

class UniqueGiftVoucherCodeGenerationFailedException extends GiftVoucherException
{
    public function __construct(?Exception $previous = null)
    {
        parent::__construct('Unable to generate a unique gift voucher code.', 0, $previous);
    }
}
