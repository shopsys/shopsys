<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Exception;

use Exception;

class FileNotFoundException extends Exception implements FileException
{
    /**
     * @param string $message
     * @param \Exception|null $previous
     */
    public function __construct($message = '', ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
