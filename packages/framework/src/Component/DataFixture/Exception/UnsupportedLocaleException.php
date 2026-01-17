<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DataFixture\Exception;

use Exception;

class UnsupportedLocaleException extends Exception
{
    /**
     * @param string $locale
     */
    public function __construct($locale, ?Exception $previous = null)
    {
        parent::__construct('Locale "' . $locale . '" is not supported.', 0, $previous);
    }
}
