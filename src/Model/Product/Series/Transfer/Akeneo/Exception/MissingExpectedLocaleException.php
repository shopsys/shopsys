<?php

declare(strict_types=1);

namespace App\Model\Product\Series\Transfer\Akeneo\Exception;

use App\Component\Akeneo\Transfer\Exception\TransferException;
use App\Model\Product\Series\Exception\ProductSeriesException;

class MissingExpectedLocaleException extends TransferException implements ProductSeriesException
{
    /**
     * @param string $attribute
     * @param string $locales
     */
    public function __construct(string $attribute, string $locales)
    {
        $message = sprintf('Missing locales "%s" for attribute "%s" in akeneo transfer product series', $locales, $attribute);

        parent::__construct($message);
    }
}
