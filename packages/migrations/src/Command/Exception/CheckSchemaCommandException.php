<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Command\Exception;

use Exception;

class CheckSchemaCommandException extends Exception
{
    /**
     * @param string $message
     */
    public function __construct($message = '', ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
