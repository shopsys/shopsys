<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Condition;

use Webmozart\Assert\Assert;

/**
 * Conditions combined by `AND` or `OR`.
 */
final readonly class Composite implements ConditionInterface
{
    /**
     * @param list<\Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface> $conditions No condition at all matches everything for `AND` and nothing for `OR`
     */
    public function __construct(
        public LogicalOperatorEnum $operator,
        public array $conditions,
    ) {
        Assert::allIsInstanceOf($conditions, ConditionInterface::class);
    }
}
