<?php

namespace Shopsys\FrameworkBundle\Form\Exception;

use Exception;

class InconsistentChoicesException extends Exception implements FormException
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
