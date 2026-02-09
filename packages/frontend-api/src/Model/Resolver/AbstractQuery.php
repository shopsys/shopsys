<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Override;
use ReflectionClass;
use ReflectionMethod;
use Shopsys\FrontendApiBundle\Component\Validation\PageSizeValidator;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractQuery implements AliasedInterface, QueryInterface
{
    protected const string QUERY_SUFFIX = 'Query';
    protected const int DEFAULT_FIRST_LIMIT = 10;

    protected PageSizeValidator $pageSizeValidator;

    #[Required]
    public function autowirePageSizeValidator(PageSizeValidator $pageSizeValidator): void
    {
        $this->pageSizeValidator = $pageSizeValidator;
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    public static function getAliases(): array
    {
        $reflectionClass = new ReflectionClass(static::class);
        $methodNames = array_map(static fn (ReflectionMethod $value): string => $value->getName(), $reflectionClass->getMethods());
        $filteredMethodNames = array_filter($methodNames, static fn (string $methodName) => str_ends_with($methodName, static::QUERY_SUFFIX));

        return array_combine($filteredMethodNames, $filteredMethodNames);
    }

    protected function setDefaultFirstOffsetIfNecessary(Argument $argument): void
    {
        if ($argument->offsetExists('first') === false && $argument->offsetExists('last') === false) {
            $argument->offsetSet('first', static::DEFAULT_FIRST_LIMIT);
        }
    }
}
