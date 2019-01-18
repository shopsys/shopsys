<?php

namespace Shopsys\FrameworkBundle\Component\Javascript\Compiler\Form\Exception;

use BadMethodCallException as PhpBadMethodCallException;
use Exception;

class BadMethodCallException extends PhpBadMethodCallException implements JsFormCompilerException
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
