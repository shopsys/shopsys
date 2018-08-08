<?php

namespace Shopsys\FrameworkBundle\Component\FileUpload\Exception;

use Exception;

class UnresolvedNamingConventionException extends Exception implements FileUploadException
{
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
