<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter;

use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;

/**
 * Everything one listing asks of its adapter — which records, identified and projected how.
 *
 * It is a value object rather than a list of arguments, so that the contract of `AdapterInterface` stays
 * put when a listing learns to ask for more.
 */
final readonly class DatasourceRequest
{
    /**
     * @param array<\Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor> $fields
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface|null $condition Null lists every record
     */
    public function __construct(
        public string $identificationName,
        public array $fields,
        public ?ConditionInterface $condition = null,
    ) {
    }
}
