<?php

namespace Shopsys\FrameworkBundle\Model\Localization\Exception;

use Exception;

class InvalidLocaleException extends Exception implements LocalizationException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
