<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Phpstan;

use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Properties\ReadWritePropertiesExtension;

/**
 * @see https://phpstan.org/developing-extensions/always-read-written-properties
 */
class GedmoExtension implements ReadWritePropertiesExtension
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

        return $this->isInitialized($property, $propertyName);
    }

    /**
     * @param \PHPStan\Reflection\PropertyReflection $property
     * @param string $propertyName
     * @return bool
     */
    public function isInitialized(PropertyReflection $property, string $propertyName): bool
    {
        return (bool)preg_match('/@Gedmo\\\Tree(Parent|Level|Left|Right)/', $property->getDocComment() ?? '');
    }
}
