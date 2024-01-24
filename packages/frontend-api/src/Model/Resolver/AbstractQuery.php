<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use ReflectionClass;
use ReflectionMethod;

abstract class AbstractQuery implements AliasedInterface, QueryInterface
{
    protected const QUERY_SUFFIX = 'Query';
    protected const DEFAULT_FIRST_LIMIT = 10;

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

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     */
    protected function setDefaultFirstOffsetIfNecessary(Argument $argument): void
    {
        if ($argument->offsetExists('first') === false && $argument->offsetExists('last') === false) {
            $argument->offsetSet('first', static::DEFAULT_FIRST_LIMIT);
        }
    }
}
