<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Phpstan;

use Override;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ClassPropertyNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class OrmPropertyHasNoTypehintRule implements Rule
{
    private const CHECKED_NAMESPACE = 'Shopsys\\';

    /**
     * @return string
     */
    #[Override]
    public function getNodeType(): string
    {
        return ClassPropertyNode::class;
    }

    /**
     * @param \PhpParser\Node $node
     * @param \PHPStan\Analyser\Scope $scope
     * @return array
     */
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

        if ($this->hasOrmMapping($node)) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'Property %s::%s has ORM mapping, so it should not have typehint.',
                    $scope->getClassReflection()?->getDisplayName(),
                    $node->getName(),
                ))->build(),
            ];
        }

        return [];
    }

    /**
     * @param \PHPStan\Node\ClassPropertyNode $node
     * @return bool
     */
    private function hasOrmMapping(ClassPropertyNode $node): bool
    {
        foreach ($node->getAttributes() as $attributeGroup) {
            if ($attributeGroup->attrGroups === null) {
                continue;
            }

            foreach ($attributeGroup->attrGroups as $attributeGroup) {
                foreach ($attributeGroup->attrs as $attribute) {
                    $attributeName = $attribute->name->toString();

                    if (str_starts_with($attributeName, 'Doctrine\ORM\Mapping')) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
