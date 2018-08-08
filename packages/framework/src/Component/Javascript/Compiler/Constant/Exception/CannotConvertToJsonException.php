<?php

namespace Shopsys\FrameworkBundle\Component\Javascript\Compiler\Constant\Exception;

use Exception;

class CannotConvertToJsonException extends Exception implements JsConstantCompilerException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
