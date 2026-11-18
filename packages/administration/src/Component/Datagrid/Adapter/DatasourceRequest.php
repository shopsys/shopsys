<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter;

use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;

/**
 * Everything one listing asks of its adapter — which records, identified and projected how.
 */
final readonly class DatasourceRequest
{
    /**
     * @param array<\Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor> $fields
     */
    public function __construct(
        public string $identificationName,
        public array $fields,
        public ?ConditionInterface $condition,
    ) {
    }
}
