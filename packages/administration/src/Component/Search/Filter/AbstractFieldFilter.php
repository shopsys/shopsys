<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search\Filter;

use LogicException;
use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;
use Shopsys\AdministrationBundle\Component\Search\ProxyQueryAwareFilterInterface;

/**
 * Base of the built-in filter types that search one or more entity fields.
 * The searched field defaults to the filter name; override it with onFields() (dot notation
 * and translated fields supported) or with a raw DQL expression via onExpression().
 */
abstract class AbstractFieldFilter implements ProxyQueryAwareFilterInterface
{
    /**
     * @var string[]
     */
    protected array $fieldPaths;

    protected ?string $expression = null;

    protected ?ProxyQuery $proxyQuery = null;

    final protected function __construct(
        protected readonly string $name,
        protected readonly string $label,
    ) {
        $this->fieldPaths = [$name];
    }

    /**
     * @param string[] $fieldPaths Field paths in dot notation; a rule matching any of them (for negative operators: all of them)
     */
    public function onFields(string ...$fieldPaths): static
    {
        $this->fieldPaths = array_values($fieldPaths);

        return $this;
    }

    /**
     * Searches a raw DQL expression instead of a field path (e.g. a CONCAT of more columns). Root alias is "o".
     */
    public function onExpression(string $expression): static
    {
        $this->expression = $expression;

        return $this;
    }

    #[Override]
    public function setProxyQuery(ProxyQuery $proxyQuery): void
    {
        $this->proxyQuery = $proxyQuery;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormOptions(): array
    {
        return [];
    }

    /**
     * @return string[] DQL expressions of the searched fields
     */
    protected function getFieldExpressions(): array
    {
        if ($this->expression !== null) {
            return [$this->expression];
        }

        return array_map(
            fn (string $fieldPath): string => $this->getProxyQuery()->getFieldExpression($fieldPath),
            $this->fieldPaths,
        );
    }

    /**
     * @return string The single DQL expression of the searched field, for filter types that do not support multiple fields
     */
    protected function getSingleFieldExpression(): string
    {
        $expressions = $this->getFieldExpressions();

        if (count($expressions) !== 1) {
            throw new LogicException(sprintf('Filter "%s" supports exactly one searched field.', $this->name));
        }

        return $expressions[0];
    }

    protected function getProxyQuery(): ProxyQuery
    {
        if ($this->proxyQuery === null) {
            throw new LogicException(sprintf('ProxyQuery was not injected into the "%s" filter before applying it.', $this->name));
        }

        return $this->proxyQuery;
    }
}
