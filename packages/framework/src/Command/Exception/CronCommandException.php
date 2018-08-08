<?php

namespace Shopsys\FrameworkBundle\Command\Exception;

use Exception;

class CronCommandException extends Exception implements CommandException
{
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
