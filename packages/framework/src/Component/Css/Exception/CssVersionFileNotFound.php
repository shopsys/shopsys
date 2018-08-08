<?php

namespace Shopsys\FrameworkBundle\Component\Css\Exception;

use Exception;

class CssVersionFileNotFound extends Exception
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', ?\Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
