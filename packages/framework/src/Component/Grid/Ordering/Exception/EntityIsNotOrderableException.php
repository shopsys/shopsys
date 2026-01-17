<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid\Ordering\Exception;

use Exception;

class EntityIsNotOrderableException extends Exception
{
    /**
     * @param string $message
     */
    public function __construct($message = '', ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
