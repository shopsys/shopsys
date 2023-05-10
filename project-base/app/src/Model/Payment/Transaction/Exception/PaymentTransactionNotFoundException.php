<?php

declare(strict_types=1);

namespace App\Model\Payment\Transaction\Exception;

use Exception;

class PaymentTransactionNotFoundException extends Exception implements PaymentTransactionExceptionInterface
{
}
