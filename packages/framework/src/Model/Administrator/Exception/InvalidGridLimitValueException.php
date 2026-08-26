<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Exception;

use Exception;
use Shopsys\FrameworkBundle\Component\Utils\Debug;

class InvalidGridLimitValueException extends Exception
{
    public function __construct(mixed $limit, ?Exception $previous = null)
    {
        parent::__construct('Administrator grid limit value ' . Debug::export($limit) . ' is invalid', 0, $previous);
    }
}
