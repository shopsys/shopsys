<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Phpstan;

use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MissingPropertyFromReflectionException;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class OrmPropertyGetterAndSetterHasNoTypehintRule implements Rule
{
    private const CHECKED_NAMESPACE = 'Shopsys\\';

    #[Override]
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof ClassMethod) {
            return [];
        }

        if (!str_starts_with($scope->getClassReflection()?->getName(), self::CHECKED_NAMESPACE)) {
            return [];
        }

        $methodName = $node->name->toString();
        $expectedPropertyName = lcfirst(substr($methodName, 3));

        try {
            $propertyReflection = $scope->getClassReflection()?->getProperty($expectedPropertyName, $scope);
        } catch (MissingPropertyFromReflectionException) {
            return [];
        }

        if (str_starts_with($methodName, 'get')) {
            return $this->checkGetter($node, $methodName, $scope, $propertyReflection);
        }

        if (str_starts_with($methodName, 'set')) {
            return $this->checkSetter($node, $methodName, $scope, $propertyReflection);
        }

        return [];
    }

    private function checkGetter(
        ClassMethod $node,
        string $methodName,
        Scope $scope,
        PropertyReflection $propertyReflection,
    ): array {
        if ($node->getReturnType() === null || $node->getReturnType()->name === 'bool') {
            return [];
        }

        if ($this->hasOrmMapping($propertyReflection) && !$this->isMethodInAncestor($methodName, $scope)) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'Method "%s::%s()" has a return typehint, but its associated property has an ORM attribute.',
                    $scope->getClassReflection()?->getName(),
                    $methodName,
                ))->line($node->getLine())->build(),
            ];
        }

        return [];
    }

    private function checkSetter(
        ClassMethod $node,
        string $methodName,
        Scope $scope,
        PropertyReflection $propertyReflection,
    ): array {
        if (count($node->getParams()) !== 1) {
            return [];
        }

        $firstParameterType = $node->getParams()[0]->type;

        if (
            !($firstParameterType instanceof Node\ComplexType) &&
            (
                $firstParameterType === null ||
                $firstParameterType->name === 'bool' ||
                $this->isParameterDataObjectOfCurrentEntity($firstParameterType->toString(), $scope)
            )
        ) {
            return [];
        }

        if ($this->hasOrmMapping($propertyReflection) && !$this->isMethodInAncestor($methodName, $scope)) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'Method "%s::%s()" has a typehint on first parameter, but its associated property has an ORM attribute.',
                    $scope->getClassReflection()?->getName(),
                    $methodName,
                ))->line($node->getLine())->build(),
            ];
        }

        return [];
    }

    private function hasOrmMapping(PropertyReflection $property): bool
    {
        $nativeReflection = $property->getNativeReflection();

        if ($nativeReflection === null) {
            return false;
        }

        foreach ($nativeReflection->getAttributes() as $attribute) {
            $attributeName = $attribute->getName();

            if (str_starts_with($attributeName, 'Doctrine\ORM\Mapping')) {
                return true;
            }
        }

        return false;
    }

    private function isMethodInAncestor(string $methodName, Scope $scope): bool
    {
        $classReflection = $scope->getClassReflection();

        if ($classReflection === null) {
            return false;
        }

        $ancestors = $classReflection->getAncestors();

        if (count($ancestors) === 0) {
            return false;
        }

        foreach ($ancestors as $ancestor) {
            if ($ancestor->is($classReflection->getName())) {
                continue;
            }

            if ($ancestor->hasMethod($methodName)) {
                return true;
            }
        }

        return false;
    }

    private function isParameterDataObjectOfCurrentEntity(string $parameterType, Scope $scope): bool
    {
        $currentClassName = $scope->getClassReflection()?->getName();

        if ($currentClassName === null) {
            return false;
        }

        return str_ends_with($parameterType, 'Data') && ($currentClassName . 'Data' === $parameterType);
    }
}
