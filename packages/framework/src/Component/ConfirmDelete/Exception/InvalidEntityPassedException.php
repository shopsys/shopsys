<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ConfirmDelete\Exception;

use Exception;

class InvalidEntityPassedException extends Exception
{
    public function __construct(string $message = '', ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
