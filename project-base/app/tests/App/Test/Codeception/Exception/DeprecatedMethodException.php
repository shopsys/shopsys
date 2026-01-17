<?php

declare(strict_types=1);

namespace Tests\App\Test\Codeception\Exception;

use Exception;

class DeprecatedMethodException extends Exception
{
    /**
     * @param string $message
     */
    public function __construct($message = '', ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
