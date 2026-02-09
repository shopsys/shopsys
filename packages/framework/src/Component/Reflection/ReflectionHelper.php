<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Reflection;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;

class ReflectionHelper
{
    /**
     * @var array<string, string[]>
     */
    protected static array $constantsIndexedByFqcn = [];

    /**
     * @return string[]
     */
    public static function getAllPublicClassConstants(string $fqcn): array
    {
        if (!array_key_exists($fqcn, self::$constantsIndexedByFqcn)) {
            self::$constantsIndexedByFqcn[$fqcn] = array_values((new ReflectionClass($fqcn))->getConstants(ReflectionClassConstant::IS_PUBLIC));
        }

        return self::$constantsIndexedByFqcn[$fqcn];
    }

    /**
     * @param class-string $fqcn
     */
    public static function getShortClassName(string $fqcn): string
    {
        return (new ReflectionClass($fqcn))->getShortName();
    }

    /**
     * @template T of object
     * @param class-string<T> $attributeClass
     * @return T|null
     */
    public static function getMethodAttribute(ReflectionMethod $method, string $attributeClass): ?object
    {
        $attributes = $method->getAttributes($attributeClass);

        if (count($attributes) > 1) {
            throw new InvalidArgumentException(sprintf('Multiple attributes of type %s found on method %s::%s', $attributeClass, $method->getDeclaringClass()->getName(), $method->getName()));
        }

        if ($attributes !== []) {
            return $attributes[0]->newInstance();
        }

        return null;
    }
}
