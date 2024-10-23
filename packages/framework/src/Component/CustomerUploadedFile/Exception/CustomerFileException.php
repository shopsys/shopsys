<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Exception;

use Exception;

class CustomerFileException extends Exception
{
    /**
     * @param string $message
     * @param \Exception|null $previous
     * @param int $code
     */
    public function __construct($message = '', ?Exception $previous = null, int $code = 0)
    {
        parent::__construct($message, $code, $previous);
    }
}
