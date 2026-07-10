<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GiftVoucher\Exception;

use Exception;

class GiftVoucherAlreadyRedeemedException extends GiftVoucherException
{
    public function __construct(string $code, ?Exception $previous = null)
    {
        parent::__construct(sprintf('Gift voucher with code "%s" is already redeemed.', $code), 0, $previous);
    }
}
