<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use ReflectionClass;
use ReflectionMethod;

abstract class AbstractMutation implements MutationInterface, AliasedInterface
{
    protected const MUTATION_SUFFIX = 'Mutation';

    /**
     * @return array
     */
    public static function getAliases(): array
    {
        $reflectionClass = new ReflectionClass(static::class);
        $methodNames = array_map(fn (ReflectionMethod $value): string => $value->getName(), $reflectionClass->getMethods());
        $filteredMethodNames = array_filter($methodNames, fn (string $methodName) => str_ends_with($methodName, static::MUTATION_SUFFIX));

        return array_combine($filteredMethodNames, $filteredMethodNames);
    }
}
