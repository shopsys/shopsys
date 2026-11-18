<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;

/**
 * The configuration of one datagrid as the current administrator sees it, created by `Datagrid::getConfig()`.
 *
 * The fields and the filters are only those the administrator is allowed to see, so no feature can tell what
 * a hidden field holds. Treat them as read-only, the datagrid displays these very fields.
 */
final readonly class DatagridConfig
{
    /**
     * @param string $gridId The ID of the datagrid, its forms are named after it so that two datagrids on one page never clash
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor[] $fields
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection $filters Including a `GuessedFilter` for each filterable field, not yet resolved against the adapter
     */
    public function __construct(
        public string $gridId,
        public AdapterInterface $adapter,
        public array $fields,
        public FilterCollection $filters,
        public DomainControlConfig $domainControlConfig = new DomainControlConfig(),
    ) {
    }

    /**
     * Replaces every character but a letter, a digit and an underscore — `product-list` + `_filter` →
     * `product_list_filter`. The ID of a datagrid is free text, a form name allows only some characters.
     */
    public function createFormName(string $suffix): string
    {
        return preg_replace('/[^A-Za-z0-9_]/', '_', $this->gridId) . $suffix;
    }
}
