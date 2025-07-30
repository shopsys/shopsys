<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\Exception;

use Exception;

class SystemManagedRoleGroupException extends Exception
{
    /**
     * @param string $name
     * @param \Exception|null $previous
     */
    public function __construct(string $name, ?Exception $previous = null)
    {
        $message = sprintf('Administrator role group "%s" is system-managed and cannot be modified.', $name);

        parent::__construct($message, 0, $previous);
    }
}
