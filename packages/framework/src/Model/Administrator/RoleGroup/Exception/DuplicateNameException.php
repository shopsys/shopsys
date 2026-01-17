<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\Exception;

use Exception;

class DuplicateNameException extends Exception
{
    /**
     * @param string $name
     */
    public function __construct($name, ?Exception $previous = null)
    {
        parent::__construct('Administrator role group with name ' . $name . ' already exists.', 0, $previous);
    }
}
