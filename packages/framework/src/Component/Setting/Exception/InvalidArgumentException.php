<?php

namespace Shopsys\FrameworkBundle\Component\Setting\Exception;

use Exception;

class InvalidArgumentException extends Exception implements SettingException
{
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
