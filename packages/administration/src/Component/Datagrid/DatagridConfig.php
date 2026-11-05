<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;

/**
 * The configuration of one datagrid as the current administrator sees it, frozen by `Datagrid::getConfig()` once
 * the datagrid is configured — what the features narrowing the datagrid (see `DatagridStateFactory`) and their
 * components need to know about it.
 *
 * The fields and the filters are only those the administrator is allowed to see, so that no feature can tell what
 * a field hidden from the administrator holds. Treat them as read-only, the datagrid displays these very fields.
 */
final readonly class DatagridConfig
{
    /**
     * @param string $gridId The ID of the datagrid, its forms are named after it so that two datagrids on one page never clash
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor[] $fields The fields the administrator is allowed to see
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection $filters The declared filters the administrator is allowed to use, not yet adapted to the adapter
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlConfig|null $domainControlConfig Null when the datagrid has no domain control
     */
    public function __construct(
        public string $gridId,
        public AdapterInterface $adapter,
        public array $fields,
        public FilterCollection $filters,
        public ?DomainControlConfig $domainControlConfig = null,
    ) {
    }

    /**
     * A form name allows only letters, digits, underscores, hyphens and colons; the ID of a datagrid is free text.
     */
    public function createFormName(string $suffix): string
    {
        return preg_replace('/[^A-Za-z0-9_]/', '_', $this->gridId) . $suffix;
    }
}
