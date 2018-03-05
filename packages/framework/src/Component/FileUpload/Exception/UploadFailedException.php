<?php

namespace Shopsys\FrameworkBundle\Component\FileUpload\Exception;

use Exception;

class UploadFailedException extends Exception implements FileUploadException
{
    /**
     * @param string $message
     * @param \Exception|null $previous
     */
    public function __construct($message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
