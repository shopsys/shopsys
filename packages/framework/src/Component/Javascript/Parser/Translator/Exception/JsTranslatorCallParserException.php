<?php

namespace Shopsys\FrameworkBundle\Component\Javascript\Parser\Translator\Exception;

use Exception;
use Shopsys\FrameworkBundle\Component\Javascript\Parser\Exception\JsParserException;

class JsTranslatorCallParserException extends Exception implements JsParserException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
