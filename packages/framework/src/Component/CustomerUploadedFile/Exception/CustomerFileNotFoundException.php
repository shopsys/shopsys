<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Exception;

use Exception;

class CustomerFileNotFoundException extends CustomerFileException
{
    /**
     * @param string $message
     */
    public function __construct($message = '', ?Exception $previous = null)
    {
        parent::__construct($message, $previous, 404);
    }
}
