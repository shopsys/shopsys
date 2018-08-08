<?php

namespace Shopsys\FrameworkBundle\Component\Image\Processing\Exception;

use Exception;

class FileIsNotSupportedImageException extends Exception implements ImageProcessingException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
