<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command\Exception;

use Exception;
use Throwable;

class UnavailableMicroserviceException extends Exception
{
    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct($message = '', $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
