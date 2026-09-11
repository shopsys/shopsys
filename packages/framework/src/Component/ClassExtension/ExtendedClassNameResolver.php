<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

class ExtendedClassNameResolver
{
    /**
     * @var array<class-string, class-string>
     */
    protected static array $extendedClassNamesByClassName = [];

    /**
     * @param array<class-string, class-string> $extendedClassNamesByClassName
     */
    public static function setExtendedClassNamesByClassName(array $extendedClassNamesByClassName): void
    {
        self::$extendedClassNamesByClassName = $extendedClassNamesByClassName;
    }

    /**
     * Returns the class the container resolves the given service class to, so static calls made on the result land in the project class
     *
     * @template T of object
     * @param class-string<T> $className
     * @return class-string<T>
     */
    public static function resolve(string $className): string
    {
        return self::$extendedClassNamesByClassName[$className] ?? $className;
    }
}
