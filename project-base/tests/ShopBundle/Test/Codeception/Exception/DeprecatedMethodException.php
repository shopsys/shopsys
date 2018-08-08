<?php

namespace Tests\ShopBundle\Test\Codeception\Exception;

use Exception;

class DeprecatedMethodException extends Exception
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
