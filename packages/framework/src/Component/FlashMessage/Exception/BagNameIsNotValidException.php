<?php

namespace Shopsys\FrameworkBundle\Component\FlashMessage\Exception;

use Exception;

class BagNameIsNotValidException extends Exception implements FlashMessageException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
