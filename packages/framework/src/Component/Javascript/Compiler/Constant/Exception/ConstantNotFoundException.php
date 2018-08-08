<?php

namespace Shopsys\FrameworkBundle\Component\Javascript\Compiler\Constant\Exception;

use Exception;

class ConstantNotFoundException extends Exception implements JsConstantCompilerException
{
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
