<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Phpstan;

use Override;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use ReflectionClass;

final class EntityShouldHaveFactoryRule implements Rule
{
    private const CHECKED_NAMESPACE = 'Shopsys\\';
    private const IGNORED_SUFFIXES = [
        'Domain',
        'Translation',
        'Translations',
    ];

    #[Override]
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof InClassNode) {
            return [];
        }

        $entityClassName = $scope->getClassReflection()?->getName() ?? '';
        $factoryClassName = $entityClassName . 'Factory';

        if (!str_starts_with($entityClassName, self::CHECKED_NAMESPACE)) {
            return [];
        }

        if (!$this->isCheckedEntity($entityClassName, $node)) {
            return [];
        }

        if (class_exists($factoryClassName)) {
            if ($this->factoryUsesEntityNameResolver($factoryClassName)) {
                return [];
            }

            return [
                RuleErrorBuilder::message(sprintf(
                    'Factory %s do not use entity name resolver',
                    $factoryClassName,
                ))->identifier('shopsys.entityShouldHaveFactory')->build(),
            ];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Entity %s is missing a factory (don\'t forget to use entity name resolver)',
                $scope->getClassReflection()?->getDisplayName(),
            ))->identifier('shopsys.entityShouldHaveFactory')->build(),
        ];
    }

    private function isCheckedEntity(string $className, InClassNode $node): bool
    {
        foreach (self::IGNORED_SUFFIXES as $ignoredSuffix) {
            if (str_ends_with($className, $ignoredSuffix)) {
                return false;
            }
        }

        $classNode = $node->getOriginalNode();

        if ($classNode instanceof Node\Stmt\Class_) {
            foreach ($classNode->attrGroups as $attributeGroup) {
                foreach ($attributeGroup->attrs as $attribute) {
                    $attributeName = $attribute->name->toString();

                    if ($attributeName === 'Doctrine\ORM\Mapping\Entity') {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function factoryUsesEntityNameResolver(string $className): bool
    {
        $reflectionClass = new ReflectionClass($className);
        $constructorParameters = $reflectionClass->getConstructor()?->getParameters();

        if ($constructorParameters === null || count($constructorParameters) === 0) {
            return false;
        }

        foreach ($constructorParameters as $constructorParameter) {
            $type = $constructorParameter->getType()?->getName() ?? '';

            if ($type === 'Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver') {
                return true;
            }
        }

        return false;
    }
}
