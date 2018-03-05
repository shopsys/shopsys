<?php

namespace Shopsys\FrameworkBundle\Component\Form\Exception;

use Exception;

class InvertedChoiceNotMultipleException extends Exception implements FormException
{
    /**
     * @param string $message
     * @param \Exception|null $previous
     */
    public function __construct($message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
