<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Exception;

use Exception;

class CustomerFileNotFoundException extends CustomerFileException
{
    public function __construct(string $message = '', ?Exception $previous = null)
    {
        parent::__construct($message, $previous, 404);
    }
}
