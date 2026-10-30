<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\TwigComponent;

use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterPanel;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearch;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridState;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * Renders the controls of a datagrid above it — the quick search and the filter as tabs of one card, and the domain
 * control — each by the component of its feature, from the part of the state of the datagrid the feature added.
 */
#[AsTwigComponent(
    name: 'Admin:Grid:Controls',
    template: '@ShopsysAdministration/components/grid/controls.html.twig',
)]
final class DatagridControlsComponent
{
    public DatagridState $state;

    /**
     * The view of the datagrid — the forms carry its order and limit along.
     */
    public GridView $gridView;

    public function getQuickSearch(): ?QuickSearch
    {
        return $this->state->get(QuickSearch::class);
    }

    public function getFilter(): ?FilterPanel
    {
        return $this->state->get(FilterPanel::class);
    }

    public function getDomainControl(): ?DomainControlScope
    {
        return $this->state->get(DomainControlScope::class);
    }

    /**
     * The tab of the search narrowing the records is the one open — the quick search unless the filter applies.
     */
    public function isFilterTabOpen(): bool
    {
        $appliedSearch = $this->state->getAppliedSearch();

        if ($appliedSearch !== null) {
            return $appliedSearch instanceof FilterPanel;
        }

        return $this->getQuickSearch() === null;
    }

    /**
     * The domain control displays nothing without a choice, the domain condition applies anyway.
     */
    public function hasDomainChoice(): bool
    {
        return $this->getDomainControl()?->offersChoice() === true;
    }
}
