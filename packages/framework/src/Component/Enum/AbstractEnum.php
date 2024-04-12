<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Enum;

use Shopsys\FrameworkBundle\Component\Reflection\ReflectionHelper;

class AbstractEnum
{
    /**
     * @return string[]
     */
    public function getAllCases(): array
    {
        return ReflectionHelper::getAllPublicClassConstants(static::class);
    }
}
