<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter\TwigComponent;

use Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearch;
use Shopsys\AdministrationBundle\Component\Datagrid\Url\DatagridNarrowingUrlFactory;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Symfony\Component\Form\FormView;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * Names every condition the listing is actually narrowed by, so that a filter composed behind a closed
 * panel never narrows the records without a visible reason. The chips are read-only; the way back is the
 * single reset beside them.
 */
#[AsTwigComponent(
    name: 'Admin:Grid:ActiveFilters',
    template: '@ShopsysAdministration/components/grid/active_filters.html.twig',
)]
final class ActiveFiltersComponent
{
    /**
     * The filter form of the datagrid, null when it declares no filter.
     */
    public ?FormView $filterForm = null;

    public ?QuickSearch $quickSearch = null;

    /**
     * Whether the filter arrived with rules composed — only then is it narrowing anything.
     */
    public bool $filterActive = false;

    public function __construct(
        private readonly DatagridNarrowingUrlFactory $narrowingUrlFactory,
    ) {
    }

    /**
     * Whether anything narrows the listing at all; the bar is not rendered otherwise.
     */
    public function isNarrowed(): bool
    {
        return $this->filterActive || $this->quickSearch?->term !== null;
    }

    /**
     * Whether the administrator left a text in the quick search that the composed filter outranks — the
     * records are narrowed by the filter alone, which the bar says out loud.
     */
    public function isQuickSearchOutranked(): bool
    {
        if ($this->filterActive === false || $this->quickSearch === null) {
            return false;
        }

        $data = $this->quickSearch->form->getData();

        return $data instanceof QuickSearchFormData && trim((string)$data->text) !== '';
    }

    /**
     * The listing without one composed rule, so that a condition can be taken back without composing the
     * whole filter again.
     *
     * @param int|string $groupIndex Index of the group in the submitted filter, which is the name of its form
     * @param int|string $ruleIndex Index of the rule in the group
     */
    public function getRuleRemovalUrl(int|string $groupIndex, int|string $ruleIndex): string
    {
        return $this->narrowingUrlFactory->createUrlWithoutFilterRule(
            $this->filterForm->vars['full_name'],
            $groupIndex,
            $ruleIndex,
        );
    }

    /**
     * The listing with the searched text dropped, the composed filter left alone.
     */
    public function getQuickSearchRemovalUrl(): string
    {
        return $this->narrowingUrlFactory->createUrlWithout([$this->quickSearch->form->getName()]);
    }

    /**
     * The listing with everything the administrator narrowed it by dropped at once.
     */
    public function getResetUrl(): string
    {
        $parameterNames = [];

        if ($this->filterForm !== null) {
            $parameterNames[] = $this->filterForm->vars['full_name'];
        }

        if ($this->quickSearch !== null) {
            $parameterNames[] = $this->quickSearch->form->getName();
        }

        return $this->narrowingUrlFactory->createUrlWithout($parameterNames);
    }
}
