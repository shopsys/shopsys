<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Command\Exception;

use Exception;

class CheckSchemaCommandException extends Exception
{
    public function __construct(string $message = '', ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
