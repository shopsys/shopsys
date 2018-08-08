<?php

namespace Shopsys\FrameworkBundle\Component\Form\Exception;

use Exception;

class InvertedChoiceNotMultipleException extends Exception implements FormException
{
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
