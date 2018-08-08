<?php

namespace Shopsys\FrameworkBundle\Component\Image\Exception;

use Exception;

class ImageNotFoundException extends Exception implements ImageException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
