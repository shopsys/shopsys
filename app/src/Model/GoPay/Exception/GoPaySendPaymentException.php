<?php

declare(strict_types=1);

namespace App\Model\GoPay\Exception;

use Exception;

class GoPaySendPaymentException extends Exception implements GoPayException
{
}
