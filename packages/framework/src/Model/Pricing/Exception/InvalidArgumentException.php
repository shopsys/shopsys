<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Exception;

use Exception;
use InvalidArgumentException as BaseInvalidArgumentException;

class InvalidArgumentException extends BaseInvalidArgumentException
{
    /**
     * @param string $message
     */
    public function __construct($message = '', ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
