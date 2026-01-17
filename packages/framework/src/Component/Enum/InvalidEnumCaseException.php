<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Enum;

use Exception;

class InvalidEnumCaseException extends Exception
{
    public function __construct(string $enumClass, string $case)
    {
        parent::__construct(sprintf('Enum class "%s" does not contain "%s" case', $enumClass, $case));
    }
}
