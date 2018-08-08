<?php

namespace Shopsys\FrameworkBundle\Component\Domain\Exception;

use Exception;

class DomainQueueEmptyException extends Exception implements DomainException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', ?\Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
