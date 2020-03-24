<?php

declare(strict_types=1);

namespace App\Model\Product\Series\Transfer\Akeneo\Exception;

use App\Component\Akeneo\Transfer\Exception\TransferException;
use App\Model\Product\Series\Exception\ProductSeriesException;

class MissingRequiredAttributeException extends TransferException implements ProductSeriesException
{
    /**
     * @param mixed $attribute
     */
    public function __construct($attribute = '')
    {
        $message = sprintf('Missing attribute %s in akeneo transfer product series', $attribute);

        parent::__construct($message);
    }
}
