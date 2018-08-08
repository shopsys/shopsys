<?php

namespace Shopsys\FrameworkBundle\Component\Javascript\Parser\Exception;

use Exception;

class UnsupportedNodeException extends Exception implements JsParserException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
