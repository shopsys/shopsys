<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Phpstan;

use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Properties\ReadWritePropertiesExtension;
use Zalas\Injector\PHPUnit\TestCase\ServiceContainerTestCase;
use function str_contains;

class InjectedPropertiesInTestsExtension implements ReadWritePropertiesExtension
{
    /**
     * @param \PHPStan\Reflection\PropertyReflection $property
     * @param string $propertyName
     * @return bool
     */
    public function isAlwaysRead(PropertyReflection $property, string $propertyName): bool
    {
        return false;
    }

    /**
     * @param \PHPStan\Reflection\PropertyReflection $property
     * @param string $propertyName
     * @return bool
     */
    public function isAlwaysWritten(PropertyReflection $property, string $propertyName): bool
    {
        $declaringClass = $property->getDeclaringClass();

        if (!$declaringClass->implementsInterface(ServiceContainerTestCase::class)) {
            return false;
        }

        return $this->isInitialized($property, $propertyName);
    }

    /**
     * @param \PHPStan\Reflection\PropertyReflection $property
     * @param string $propertyName
     * @return bool
     */
    public function isInitialized(PropertyReflection $property, string $propertyName): bool
    {
        return str_contains($property->getDocComment() ?? '', '@inject');
    }
}
