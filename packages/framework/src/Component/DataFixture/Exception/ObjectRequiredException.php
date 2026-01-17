<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DataFixture\Exception;

use Exception;
use InvalidArgumentException;
use Shopsys\FrameworkBundle\Component\Utils\Debug;

class ObjectRequiredException extends InvalidArgumentException
{
    /**
     * @param mixed $given
     */
    public function __construct($given, ?Exception $previous = null)
    {
        parent::__construct('Object required, but given "' . Debug::export($given) . '"', 0, $previous);
    }
}
