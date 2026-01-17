<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Phpstan;

use Override;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Properties\ReadWritePropertiesExtension;
use Zalas\Injector\PHPUnit\TestCase\ServiceContainerTestCase;
use function str_contains;

class InjectedPropertiesInTestsExtension implements ReadWritePropertiesExtension
{
    #[Override]
    public function isAlwaysRead(PropertyReflection $property, string $propertyName): bool
    {
        return false;
    }

    #[Override]
    public function isAlwaysWritten(PropertyReflection $property, string $propertyName): bool
    {
        $declaringClass = $property->getDeclaringClass();

        if (!$declaringClass->implementsInterface(ServiceContainerTestCase::class)) {
            return false;
        }

        return $this->isInitialized($property, $propertyName);
    }

    #[Override]
    public function isInitialized(PropertyReflection $property, string $propertyName): bool
    {
        return str_contains($property->getDocComment() ?? '', '@inject');
    }
}
