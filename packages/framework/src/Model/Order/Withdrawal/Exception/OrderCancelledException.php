<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception;

use Exception;

class OrderCancelledException extends Exception implements WithdrawalException
{
}
