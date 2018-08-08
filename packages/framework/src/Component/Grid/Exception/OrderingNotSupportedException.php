<?php

namespace Shopsys\FrameworkBundle\Component\Grid\Exception;

use Exception;

class OrderingNotSupportedException extends Exception implements GridException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
