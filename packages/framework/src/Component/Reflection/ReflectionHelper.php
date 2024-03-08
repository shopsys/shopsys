<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Reflection;

use ReflectionClass;
use ReflectionClassConstant;

class ReflectionHelper
{
    /**
     * @var array<string, string[]>
     */
    protected static array $constantsIndexedByFqcn = [];

    /**
     * @param string $fqcn
     * @return string[]
     */
    public static function getAllPublicClassConstants(string $fqcn): array
    {
        if (array_key_exists($fqcn, self::$constantsIndexedByFqcn) === false) {
            self::$constantsIndexedByFqcn[$fqcn] = array_values((new ReflectionClass($fqcn))->getConstants(ReflectionClassConstant::IS_PUBLIC));
        }

        return self::$constantsIndexedByFqcn[$fqcn];
    }
}
