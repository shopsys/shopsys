<?php

namespace Shopsys\FrameworkBundle\Model\Cart\Exception;

use Exception;

class SessionIsNullException extends Exception implements CartException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(Exception $previous = null)
    {
        parent::__construct('Session is NULL. Check if Redis is available.', 0, $previous);
    }
}
