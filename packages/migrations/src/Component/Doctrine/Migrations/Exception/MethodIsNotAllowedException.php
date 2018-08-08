<?php

namespace Shopsys\MigrationBundle\Component\Doctrine\Migrations\Exception;

use Exception;

class MethodIsNotAllowedException extends Exception
{
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
