<?php

declare(strict_types=1);

namespace App\Twig\Gtm\Exception;

use Exception;
use Shopsys\FrameworkBundle\Twig\Exception\TwigException;

class InvalidProductObjectTypeException extends Exception implements TwigException
{
    /**
     * {@inheritdoc}
     */
    public function __construct($objectType, ?Exception $previous = null)
    {
        $message = sprintf('Object type "%s" is not a valid product object type.', gettype($objectType));

        parent::__construct($message, 0, $previous);
    }
}
