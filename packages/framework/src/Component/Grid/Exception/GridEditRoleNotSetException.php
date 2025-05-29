<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid\Exception;

use Exception;

class GridEditRoleNotSetException extends Exception
{
    /**
     * @param string $message
     */
    public function __construct(
        string $message = 'Grid edit role has to be set for correct displaying of delete columns.',
    ) {
        parent::__construct($message);
    }
}
