<?php

declare(strict_types=1);

namespace Tests\App\Test\Codeception\Exception;

use Exception;

class DeprecatedMethodException extends Exception
{
    public function __construct(string $message = '', ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
