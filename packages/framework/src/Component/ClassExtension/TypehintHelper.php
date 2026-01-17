<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

use Roave\BetterReflection\Reflection\ReflectionNamedType;
use Roave\BetterReflection\Reflection\ReflectionProperty;

class TypehintHelper
{
    public function getPropertyTypeFromTypehint(ReflectionProperty $reflectionProperty): ?string
    {
        $type = $reflectionProperty->getType();

        if (($type instanceof ReflectionNamedType) === false) {
            return null;
        }

        return '\\' . $type->getName();
    }
}
