<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Phpstan;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use ReflectionClass;

class EntityShouldHaveFactoryRule implements Rule
{
    private const CHECKED_NAMESPACE = 'Shopsys\\';
    private const IGNORED_SUFFIXES = [
        'Domain',
        'Translation',
    ];

    /**
     * @return string
     */
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * @param \PhpParser\Node $node
     * @param \PHPStan\Analyser\Scope $scope
     * @return array
     */
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
                ))->build(),
            ];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Entity %s is missing a factory (don\'t forget to use entity name resolver)',
                $scope->getClassReflection()?->getDisplayName(),
            ))->build(),
        ];
    }

    /**
     * @param string $className
     * @param \PHPStan\Node\InClassNode $node
     * @return bool
     */
    private function isCheckedEntity(string $className, InClassNode $node): bool
    {
        foreach (self::IGNORED_SUFFIXES as $ignoredSuffix) {
            if (str_ends_with($className, $ignoredSuffix)) {
                return false;
            }
        }

        return str_contains($node->getDocComment()?->getText() ?? '', '@ORM\Entity');
    }

    /**
     * @param string $className
     * @return bool
     */
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
