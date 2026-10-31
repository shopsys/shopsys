<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter\TwigComponent;

use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterPanel;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\FilterFormFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Symfony\Component\Form\FormView;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * Renders the filter of a datagrid — the composed groups of rules, and the prototypes the page swaps in
 * when the administrator adds a rule or changes its filter or operation. Sent by GET, so the composed
 * filter lives in the URL; the order and the limit of the grid travel along, the page starts over.
 *
 * The form of the rules is the one submitted while the state of the datagrid was created, so that its errors
 * are rendered; the prototypes decide nothing, so the component builds them itself.
 */
#[AsTwigComponent(
    name: 'Admin:Grid:Filters',
    template: '@ShopsysAdministration/components/grid/filters.html.twig',
)]
final class FiltersComponent
{
    public FilterPanel $filter;

    public GridView $gridView;

    /**
     * Rendered inside a card of the page (a tab next to the quick search) instead of a card of its own.
     */
    public bool $embedded = false;

    private ?FormView $formView = null;

    private ?FormView $prototypesFormView = null;

    public function __construct(
        private readonly FilterFormFactory $filterFormFactory,
    ) {
    }

    public function getFormView(): FormView
    {
        return $this->formView ??= $this->filter->form->createView();
    }

    public function getPrototypesFormView(): FormView
    {
        return $this->prototypesFormView ??= $this->filterFormFactory
            ->createPrototypesForm($this->filter->filters, $this->filter->form->getName())
            ->createView();
    }

    /**
     * Whether any rule was composed — the panel is then marked as active.
     */
    public function isActive(): bool
    {
        return $this->filter->isActive();
    }
}
