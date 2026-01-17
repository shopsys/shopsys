<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Processing\Exception;

use Exception;

class FileIsNotSupportedImageException extends Exception
{
    /**
     * @param string $message
     */
    public function __construct($message = '', ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
