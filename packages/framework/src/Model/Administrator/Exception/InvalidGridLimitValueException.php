<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Exception;

use Exception;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\Utils\Debug;

class InvalidGridLimitValueException extends Exception
{
    public function __construct(mixed $limit, ?Exception $previous = null)
    {
        parent::__construct('Administrator grid limit value ' . ExtendedClassNameResolver::resolve(Debug::class)::export($limit) . ' is invalid', 0, $previous);
    }
}
