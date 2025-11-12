<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WithdrawalRequestNotFoundException extends NotFoundHttpException
{
}
