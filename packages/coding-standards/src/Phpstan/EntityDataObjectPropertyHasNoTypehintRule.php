<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Phpstan;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ClassPropertyNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use ReflectionClass;
use ReflectionException;

class EntityDataObjectPropertyHasNoTypehintRule implements Rule
{
    private const CHECKED_NAMESPACE = 'Shopsys\\';

    /**
     * @return string
     */
    public function getNodeType(): string
    {
        return ClassPropertyNode::class;
    }

    /**
     * @param \PhpParser\Node $node
     * @param \PHPStan\Analyser\Scope $scope
     * @return array
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof ClassPropertyNode) {
            return [];
        }

        if (!str_starts_with($scope->getClassReflection()?->getName(), self::CHECKED_NAMESPACE)) {
            return [];
        }

        if (!$this->isDataObjectWithAssociatedEntity($scope)) {
            return [];
        }

        if ($node->getNativeType() === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Property %s::%s on data object with associated entity, should not have typehint.',
                $scope->getClassReflection()?->getDisplayName(),
                $node->getName(),
            ))->build(),
        ];
    }

    /**
     * @param \PHPStan\Analyser\Scope $scope
     * @return bool
     */
    private function isDataObjectWithAssociatedEntity(Scope $scope): bool
    {
        $className = $scope->getClassReflection()?->getName();

        if (!str_ends_with($className, 'Data')) {
            return false;
        }

        try {
            $reflectionClass = new ReflectionClass(substr($className, 0, -4));
            $docComment = $reflectionClass->getDocComment();

            return str_contains($docComment, '@ORM\Entity');
        } catch (ReflectionException) {
            return false;
        }
    }
}
