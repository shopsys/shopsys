<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Override;
use Traversable;

/**
 * All rules of one advanced search subject, handed to the filter at once so it can decide
 * how multiple rules combine (e.g. OR them into one IN condition).
 *
 * @implements \IteratorAggregate<int, \Shopsys\AdministrationBundle\Component\Search\FilterRule>
 */
final class FilterRuleCollection implements IteratorAggregate, Countable
{
    /**
     * @var array<string, string[]>
     */
    private array $ruleErrorsByFormIndex = [];

    /**
     * @param \Shopsys\AdministrationBundle\Component\Search\FilterRule[] $rules
     */
    public function __construct(
        private readonly array $rules,
    ) {
    }

    /**
     * @return \Traversable<int, \Shopsys\AdministrationBundle\Component\Search\FilterRule>
     */
    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->rules);
    }

    #[Override]
    public function count(): int
    {
        return count($this->rules);
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Search\FilterRule[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Reports a problem with a rule (e.g. a referenced record does not exist).
     * The message is displayed as a form error on the rule row instead of failing the search.
     */
    public function addRuleError(FilterRule $rule, string $message): void
    {
        $this->ruleErrorsByFormIndex[$rule->getFormIndex()][] = $message;
    }

    /**
     * @return array<string, string[]> Error messages indexed by the rule's form index
     */
    public function getRuleErrors(): array
    {
        return $this->ruleErrorsByFormIndex;
    }
}
