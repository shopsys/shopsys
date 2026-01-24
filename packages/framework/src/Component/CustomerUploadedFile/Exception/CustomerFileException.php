<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Exception;

use Exception;

class CustomerFileException extends Exception
{
    public function __construct(string $message = '', ?Exception $previous = null, int $code = 0)
    {
        parent::__construct($message, $code, $previous);
    }
}
