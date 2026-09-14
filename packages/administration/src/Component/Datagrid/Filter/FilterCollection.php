<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\FilterNotFoundException;
use Traversable;

/**
 * A set of filters by their names — those declared on a datagrid by
 * `$datagrid->filters()->add(TextFilter::new('author.fullName'))`, or those offered to the administrator.
 *
 * @implements \IteratorAggregate<string, \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterInterface>
 */
final class FilterCollection implements IteratorAggregate
{
    /**
     * @var array<string, \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterInterface>
     */
    private array $filters = [];

    /**
     * @return $this
     */
    public function add(FilterInterface $filter): self
    {
        if (array_key_exists($filter->getName(), $this->filters)) {
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

    /**
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FilterNotFoundException
     */
    public function get(string $name): FilterInterface
    {
        return $this->filters[$name] ?? throw new FilterNotFoundException($name);
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
