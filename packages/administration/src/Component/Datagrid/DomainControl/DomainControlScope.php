<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\DomainControl;

use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;

/**
 * The domain control of one datagrid — the domains it works with, the way the administrator picks
 * among them, and how the datagrid relates to a domain.
 */
final readonly class DomainControlScope
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlType $type Never `NONE` — a datagrid without a domain control has no scope
     * @param int[] $domainIds Domains available to the datagrid
     * @param int|null $selectedDomainId Null when "All domains" is selected in the domain filter
     * @param string|null $filterNamespace Null for the domain switcher
     * @param string|null $domainIdPath Path to the domain ID of the listed entity, null when the entity does not belong to a domain
     */
    public function __construct(
        public DomainControlType $type,
        public array $domainIds,
        public ?int $selectedDomainId,
        public ?string $filterNamespace,
        public ?string $domainIdPath = null,
    ) {
    }

    public function isFilter(): bool
    {
        return $this->type === DomainControlType::FILTER;
    }

    /**
     * Tells whether the datagrid can be limited to the domains of this scope — false when the listed
     * entity does not belong to a domain, in which case the control only offers the choice.
     */
    public function isQueryFiltered(): bool
    {
        return $this->domainIdPath !== null;
    }

    /**
     * Tells whether the domain of a record is worth displaying, which it is not when the whole
     * datagrid works with a single domain anyway.
     */
    public function isDomainWorthDisplaying(): bool
    {
        return $this->isQueryFiltered() && count($this->domainIds) > 1;
    }

    /**
     * Returns the domain IDs the datagrid is allowed to show: the selected domain,
     * or all domains available to the datagrid when "All domains" is selected.
     *
     * @return int[]
     */
    public function getEffectiveDomainIds(): array
    {
        return $this->selectedDomainId !== null ? [$this->selectedDomainId] : $this->domainIds;
    }

    /**
     * Returns the condition limiting the datagrid to the domains of this scope, or null when the listed
     * entity does not belong to a domain. An administrator without any domain gets an empty list, which
     * matches nothing.
     */
    public function createCondition(): ?ConditionInterface
    {
        if ($this->domainIdPath === null) {
            return null;
        }

        return Condition::in($this->domainIdPath, $this->getEffectiveDomainIds());
    }
}
