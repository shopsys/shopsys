<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use ReflectionClass;
use ReflectionMethod;

abstract class AbstractQuery implements AliasedInterface, QueryInterface
{
    protected const QUERY_SUFFIX = 'Query';

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        $reflectionClass = new ReflectionClass(static::class);
        $methodNames = array_map(fn (ReflectionMethod $value): string => $value->getName(), $reflectionClass->getMethods());
        $filteredMethodNames = array_filter($methodNames, fn (string $methodName) => str_ends_with($methodName, static::QUERY_SUFFIX));

        return array_combine($filteredMethodNames, $filteredMethodNames);
    }
}
