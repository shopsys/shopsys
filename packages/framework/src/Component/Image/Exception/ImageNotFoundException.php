<?php

namespace Shopsys\FrameworkBundle\Component\Image\Exception;

use Exception;

class ImageNotFoundException extends Exception implements ImageException
{
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
