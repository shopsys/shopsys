<?php

namespace Shopsys\FrameworkBundle\Component\Javascript\Parser\Constant\Exception;

use Exception;
use Shopsys\FrameworkBundle\Component\Javascript\Parser\Exception\JsParserException;

class JsConstantCallParserException extends Exception implements JsParserException
{
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
