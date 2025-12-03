<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception;

use Exception;

class OrderNotFoundForWithdrawalException extends Exception implements WithdrawalException
{
}
