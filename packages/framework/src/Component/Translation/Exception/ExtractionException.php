<?php

namespace Shopsys\FrameworkBundle\Component\Translation\Exception;

use Exception;

class ExtractionException extends Exception implements TranslationException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
