<?php

namespace Shopsys\FrameworkBundle\Model\Feed\Exception;

use Exception;

class FeedNotFoundException extends Exception implements FeedException
{
    public function __construct(string $name, Exception $previous = null)
    {
        $message = 'Feed with name "' . $name . ' not found.';

        parent::__construct($message, 0, $previous);
    }
}
