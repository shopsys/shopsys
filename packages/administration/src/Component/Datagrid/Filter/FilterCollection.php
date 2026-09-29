<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\FilterNotFoundException;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterFormData;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterGroupData;
use Traversable;

/**
 * The filters declared on one datagrid — `$datagrid->filters()->add(TextFilter::new('author.fullName'))`.
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
        return $this->filters[array_key_first($this->filters)] ?? null;
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

    /**
     * Adapts every filter to the datagrid it is declared on.
     *
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FilterNotApplicableException
     */
    public function resolveFor(FilterEnvironment $environment): void
    {
        foreach ($this->filters as $filter) {
            $filter->resolveFor($environment);
        }
    }

    /**
     * The condition of everything the administrator composed: the rules of a group combined by the operator
     * of the group, the groups by the operator of the form. A rule of an unknown filter or without a value
     * narrows nothing; null when nothing narrows at all.
     */
    public function createCondition(FilterFormData $data): ?ConditionInterface
    {
        $groupConditions = [];

        foreach ($data->groups as $group) {
            $ruleConditions = [];

            foreach ($group->rules as $rule) {
                if ($rule->filter === null || $this->has($rule->filter) === false) {
                    continue;
                }

                $ruleCondition = $this->get($rule->filter)->buildCondition($rule);

                if ($ruleCondition !== null) {
                    $ruleConditions[] = $ruleCondition;
                }
            }

            if ($ruleConditions !== []) {
                $groupConditions[] = $this->compose($group->operator, $ruleConditions);
            }
        }

        return $groupConditions === [] ? null : $this->compose($data->operator, $groupConditions);
    }

    /**
     * @param string|null $operator Null (the operator was not submitted) combines by AND, the way the form starts
     * @param list<\Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface> $conditions
     */
    private function compose(?string $operator, array $conditions): ConditionInterface
    {
        if (count($conditions) === 1) {
            return $conditions[0];
        }

        return $operator === FilterGroupData::OPERATOR_OR ? Condition::orX(...$conditions) : Condition::andX(...$conditions);
    }
}
