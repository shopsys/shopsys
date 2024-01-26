<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Phpstan;

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

    /**
     * @return string
     */
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /**
     * @param \PhpParser\Node $node
     * @param \PHPStan\Analyser\Scope $scope
     * @return array
     */
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

    /**
     * @param \PhpParser\Node\Stmt\ClassMethod $node
     * @param string $methodName
     * @param \PHPStan\Analyser\Scope $scope
     * @param \PHPStan\Reflection\PropertyReflection $propertyReflection
     * @return array
     */
    private function checkGetter(
        ClassMethod $node,
        string $methodName,
        Scope $scope,
        PropertyReflection $propertyReflection,
    ): array {
        if ($node->getReturnType() === null || $node->getReturnType()->name === 'bool') {
            return [];
        }

        if ($this->hasOrmAnnotation($propertyReflection) && !$this->isMethodInAncestor($methodName, $scope)) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'Method "%s::%s()" has a return typehint, but its associated property has an ORM annotation.',
                    $scope->getClassReflection()?->getName(),
                    $methodName,
                ))->line($node->getLine())->build(),
            ];
        }

        return [];
    }

    /**
     * @param \PhpParser\Node\Stmt\ClassMethod $node
     * @param string $methodName
     * @param \PHPStan\Analyser\Scope $scope
     * @param \PHPStan\Reflection\PropertyReflection $propertyReflection
     * @return array
     */
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

        if ($this->hasOrmAnnotation($propertyReflection) && !$this->isMethodInAncestor($methodName, $scope)) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'Method "%s::%s()" has a typehint on first parameter, but its associated property has an ORM annotation.',
                    $scope->getClassReflection()?->getName(),
                    $methodName,
                ))->line($node->getLine())->build(),
            ];
        }

        return [];
    }

    /**
     * @param \PHPStan\Reflection\Php\PhpPropertyReflection $property
     * @return bool
     */
    private function hasOrmAnnotation(PropertyReflection $property): bool
    {
        return $property->getDocComment() !== null && str_contains($property->getDocComment(), '@ORM\\');
    }

    /**
     * @param string $methodName
     * @param \PHPStan\Analyser\Scope $scope
     * @return bool
     */
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

    /**
     * @param string $parameterType
     * @param \PHPStan\Analyser\Scope $scope
     * @return bool
     */
    private function isParameterDataObjectOfCurrentEntity(string $parameterType, Scope $scope): bool
    {
        $currentClassName = $scope->getClassReflection()?->getName();

        if ($currentClassName === null) {
            return false;
        }

        return str_ends_with($parameterType, 'Data') && ($currentClassName . 'Data' === $parameterType);
    }
}
