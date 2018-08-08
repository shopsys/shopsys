<?php

namespace Shopsys\FrameworkBundle\Component\Grid\Ordering\Exception;

use Exception;

class EntityIsNotOrderableException extends Exception implements OrderingException
{
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
