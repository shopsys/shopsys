<?php

namespace Shopsys\FrameworkBundle\Component\Javascript\Compiler\Form\Exception;

use Exception;

class CannotParseFormTypeException extends Exception implements JsFormCompilerException
{
    /**
     * @param string $message
     * @param \Exception|null $previous
     */
    public function __construct(string $message, Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
