<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionNamedType;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Roave\BetterReflection\Reflection\ReflectionUnionType;

class TypehintHelper
{
    public function getPropertyTypeFromTypehint(ReflectionProperty $reflectionProperty): ?string
    {
        return $this->formatReflectionType($reflectionProperty->getType());
    }

    public function getParameterTypeFromTypehint(ReflectionParameter $reflectionParameter): ?string
    {
        $type = $reflectionParameter->getType();

        return $this->formatReflectionType($type);
    }

    public function getMethodReturnTypeFromTypehint(ReflectionMethod $reflectionMethod): ?string
    {
        $type = $reflectionMethod->getReturnType();

        return $this->formatReflectionType($type);
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionNamedType|\Roave\BetterReflection\Reflection\ReflectionUnionType|\Roave\BetterReflection\Reflection\ReflectionIntersectionType|null $type
     */
    protected function formatReflectionType($type): ?string
    {
        if ($type === null) {
            return null;
        }

        if ($type instanceof ReflectionUnionType) {
            $typeStrings = [];

            foreach ($type->getTypes() as $subType) {
                if ($subType instanceof ReflectionNamedType) {
                    $name = $subType->getName();
                    $typeStrings[] = $subType->isBuiltin() ? $name : '\\' . $name;
                }
            }

            return implode('|', $typeStrings);
        }

        if ($type instanceof ReflectionNamedType) {
            $name = $type->getName();
            $typeString = $type->isBuiltin() ? $name : '\\' . $name;

            if ($type->allowsNull() && $name !== 'null' && $name !== 'mixed') {
                return $typeString . '|null';
            }

            return $typeString;
        }

        return null;
    }
}
