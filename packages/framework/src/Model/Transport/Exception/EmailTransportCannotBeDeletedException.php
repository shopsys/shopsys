<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport\Exception;

use Exception;

class EmailTransportCannotBeDeletedException extends Exception
{
    public function __construct(
        string $message = 'Transport of type email cannot be deleted.',
        ?Exception $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
