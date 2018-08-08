<?php

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Exception;

use Exception;

class EntityIdentifierException extends Exception implements FileException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
