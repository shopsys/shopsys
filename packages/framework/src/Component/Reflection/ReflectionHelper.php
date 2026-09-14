<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Reflection;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;
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
     * Returns the single attribute of the method, looked up along the prototype chain (see getMethodAttributes())
     *
     * @template T of object
     * @param class-string<T> $attributeClass
     * @return T|null
     */
    public static function getMethodAttribute(ReflectionMethod $method, string $attributeClass): ?object
    {
        $attributes = self::getMethodAttributes($method, $attributeClass);

        if (count($attributes) > 1) {
            throw new InvalidArgumentException(sprintf('Multiple attributes of type %s found on method %s::%s', $attributeClass, $method->getDeclaringClass()->getName(), $method->getName()));
        }

        return $attributes[0] ?? null;
    }

    /**
     * Attributes are not inherited by overriding methods, so the attributes of the nearest declaration
     * in the prototype chain that carries them are returned (an override without attributes keeps the attributes of the parent method)
     *
     * @template T of object
     * @param class-string<T> $attributeClass
     * @param int $flags see ReflectionMethod::getAttributes()
     * @return list<T>
     */
    public static function getMethodAttributes(ReflectionMethod $method, string $attributeClass, int $flags = 0): array
    {
        $currentMethod = $method;

        while (true) {
            $attributes = $currentMethod->getAttributes($attributeClass, $flags);

            if ($attributes !== []) {
                return array_map(static fn ($attribute) => $attribute->newInstance(), $attributes);
            }

            try {
                $currentMethod = $currentMethod->getPrototype();
            } catch (ReflectionException) {
                return [];
            }
        }
    }

    /**
     * Returns the single attribute of the class or of its nearest parent class that carries it
     *
     * @template T of object
     * @param class-string<T> $attributeClass
     * @return T|null
     */
    public static function getClassAttribute(ReflectionClass $class, string $attributeClass): ?object
    {
        for ($currentClass = $class; $currentClass !== false; $currentClass = $currentClass->getParentClass()) {
            $attributes = $currentClass->getAttributes($attributeClass);

            if ($attributes !== []) {
                return $attributes[0]->newInstance();
            }
        }

        return null;
    }
}
