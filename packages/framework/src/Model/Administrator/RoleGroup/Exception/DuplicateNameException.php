<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\AdministratorException;

class DuplicateNameException extends Exception implements AdministratorException
{
    /**
     * @param string $name
     * @param \Exception|null $previous
     */
    public function __construct($name, ?Exception $previous = null)
    {
        parent::__construct('Administrator role group with name ' . $name . ' already exists.', 0, $previous);
    }
}
