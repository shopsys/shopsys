<?php

namespace Shopsys\FrameworkBundle\Model\Order\Exception;

use Exception;

class OrderHashGenerateException extends Exception implements OrderException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
