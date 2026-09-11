<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

use Shopsys\FrameworkBundle\Component\ClassExtension\Exception\ExtendedClassNamesAlreadySetException;

class ExtendedClassNameResolver
{
    /**
     * @var array<class-string, class-string>|null
     */
    protected static ?array $extendedClassNamesByClassName = null;

    /**
     * @internal Called once by ShopsysFrameworkBundle::boot() with the map built at container compile time, the map cannot be replaced afterwards
     * @param array<class-string, class-string> $extendedClassNamesByClassName
     */
    public static function setExtendedClassNamesByClassName(array $extendedClassNamesByClassName): void
    {
        if (self::$extendedClassNamesByClassName !== null) {
            throw new ExtendedClassNamesAlreadySetException();
        }

        self::$extendedClassNamesByClassName = $extendedClassNamesByClassName;
    }

    /**
     * @internal Called by ShopsysFrameworkBundle::shutdown() so that the next kernel boot can set the map again
     */
    public static function reset(): void
    {
        self::$extendedClassNamesByClassName = null;
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
