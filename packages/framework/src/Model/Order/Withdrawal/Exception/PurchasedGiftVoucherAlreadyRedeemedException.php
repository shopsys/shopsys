<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception;

use Exception;

class PurchasedGiftVoucherAlreadyRedeemedException extends Exception implements WithdrawalException
{
}
