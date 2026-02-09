<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Exception;

use Exception;

class ImageNotFoundException extends Exception
{
    public function __construct(string $message = '', ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
