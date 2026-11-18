<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use Override;
use Traversable;

/**
 * @implements \IteratorAggregate<string, \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterInterface>
 */
final class FilterCollection implements IteratorAggregate
{
    /**
     * @var array<string, \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterInterface>
     */
    private array $filters = [];

    /**
     * Replaces a `GuessedFilter` of the same name, so a declared filter takes precedence over the one picked for
     * a field. Fails for any other filter of the same name.
     *
     * @return $this
     */
    public function add(FilterInterface $filter): self
    {
        if ($this->has($filter->getName()) && $this->filters[$filter->getName()] instanceof GuessedFilter === false) {
            throw new InvalidArgumentException(sprintf('Filter with name "%s" already exists, give one of them another name by setName().', $filter->getName()));
        }

        $this->filters[$filter->getName()] = $filter;

        return $this;
    }

    /**
     * @return $this
     */
    public function remove(string $name): self
    {
        $this->get($name);
        unset($this->filters[$name]);

        return $this;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->filters);
    }

    public function get(string $name): FilterInterface
    {
        return $this->filters[$name] ?? throw new InvalidArgumentException(sprintf('Filter "%s" is not declared on the datagrid.', $name));
    }

    public function first(): ?FilterInterface
    {
        $firstName = array_key_first($this->filters);

        return $firstName === null ? null : $this->filters[$firstName];
    }

    public function isEmpty(): bool
    {
        return $this->filters === [];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->filters);
    }
}
