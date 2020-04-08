<?php

declare(strict_types=1);

namespace App\Twig\Cache\Exception;

use Exception;
use Shopsys\FrameworkBundle\Twig\Exception\TwigException;

class InvalidCacheLifetimeException extends Exception implements TwigException
{
    /**
     * {@inheritdoc}
     */
    public function __construct($lifetime, ?Exception $previous = null)
    {
        $message = sprintf('Value of type "%s" is not a valid lifetime.', gettype($lifetime));

        parent::__construct($message, 0, $previous);
    }
}
