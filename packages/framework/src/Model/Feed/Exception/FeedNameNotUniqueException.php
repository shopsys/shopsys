<?php

namespace Shopsys\FrameworkBundle\Model\Feed\Exception;

use Exception;

class FeedNameNotUniqueException extends Exception implements FeedException
{
    public function __construct(string $name, Exception $previous = null)
    {
        $message = 'Feed with name "' . $name . ' is already registered. Feed names must be unique.';

        parent::__construct($message, 0, $previous);
    }
}
