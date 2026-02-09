<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\Exception;

use Exception;

class DuplicateNameException extends Exception
{
    public function __construct(string $name, ?Exception $previous = null)
    {
        parent::__construct('Administrator role group with name ' . $name . ' already exists.', 0, $previous);
    }
}
