<?php

namespace Shopsys\FrameworkBundle\Form\Exception;

use Exception;

class MissingRouteNameException extends Exception implements FormException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
