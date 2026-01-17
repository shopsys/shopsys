<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Phpstan;

use Override;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ClassPropertyNode;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class OrmPropertyHasNoTypehintRule implements Rule
{
    private const CHECKED_NAMESPACE = 'Shopsys\\';

    #[Override]
    public function getNodeType(): string
    {
        return ClassPropertyNode::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof ClassPropertyNode) {
            return [];
        }

        if (!str_starts_with($scope->getClassReflection()?->getName(), self::CHECKED_NAMESPACE)) {
            return [];
        }

        if ($node->getNativeType() === null) {
            return [];
        }

        $propertyReflection = $scope->getClassReflection()?->getProperty($node->getName(), $scope);

        if ($this->hasOrmAnnotation($propertyReflection)) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'Property %s::%s has ORM annotation, so it should not have typehint.',
                    $scope->getClassReflection()?->getDisplayName(),
                    $node->getName(),
                ))->build(),
            ];
        }

        return [];
    }

    private function hasOrmAnnotation(PropertyReflection $property): bool
    {
        return $property->getDocComment() !== null && str_contains($property->getDocComment(), '@ORM\\');
    }
}
