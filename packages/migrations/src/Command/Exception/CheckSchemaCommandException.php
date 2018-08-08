<?php

namespace Shopsys\MigrationBundle\Command\Exception;

use Exception;

class CheckSchemaCommandException extends Exception
{

    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
