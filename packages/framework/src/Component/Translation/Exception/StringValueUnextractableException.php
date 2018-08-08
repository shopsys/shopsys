<?php

namespace Shopsys\FrameworkBundle\Component\Translation\Exception;

use Exception;

class StringValueUnextractableException extends Exception implements TranslationException
{
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
