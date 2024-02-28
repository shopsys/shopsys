<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Reflection;

use ReflectionClass;
use ReflectionClassConstant;

class ReflectionHelper
{
    /**
     * @param string $fqcn
     * @return array
     */
    public static function getAllPublicClassConstants(string $fqcn): array
    {
        return array_values((new ReflectionClass($fqcn))->getConstants(ReflectionClassConstant::IS_PUBLIC));
    }
}
