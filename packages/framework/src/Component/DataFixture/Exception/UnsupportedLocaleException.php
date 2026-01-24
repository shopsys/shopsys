<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DataFixture\Exception;

use Exception;

class UnsupportedLocaleException extends Exception
{
    public function __construct(string $locale, ?Exception $previous = null)
    {
        parent::__construct('Locale "' . $locale . '" is not supported.', 0, $previous);
    }
}
