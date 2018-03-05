<?php

namespace Shopsys\FrameworkBundle\Command\Exception;

use Exception;

class NoDomainSetCommandException extends Exception implements CommandException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(Exception $previous = null)
    {
        $message = 'There are no domains set.';
        parent::__construct($message, 0, $previous);
    }
}
