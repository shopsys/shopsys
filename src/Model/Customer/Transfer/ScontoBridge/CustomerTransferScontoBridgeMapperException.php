<?php

declare(strict_types=1);

namespace App\Model\Customer\Transfer\ScontoBridge;

use App\Component\ScontoBridge\Transfer\Exception\TransferException;
use Throwable;

class CustomerTransferScontoBridgeMapperException extends TransferException
{
    public function __construct(string $message, Throwable $prev = null)
    {
        parent::__construct($message, 0, $prev);
    }
}
