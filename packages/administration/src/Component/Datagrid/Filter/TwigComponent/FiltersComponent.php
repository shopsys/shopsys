<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter\TwigComponent;

use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Symfony\Component\Form\FormView;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * Renders the filter of a datagrid — the composed groups of rules, and the prototypes the page swaps in
 * when the administrator adds a rule or changes its filter or operation. Sent by GET, so the composed
 * filter lives in the URL; the order and the limit of the grid travel along, the page starts over.
 */
#[AsTwigComponent(
    name: 'Admin:Grid:Filters',
    template: '@ShopsysAdministration/components/grid/filters.html.twig',
)]
final class FiltersComponent
{
    /**
     * The view of the submitted filter form — the page passes the form itself among the parameters of `render()`,
     * which turns it into its view and answers 422 when the composed filter is invalid.
     */
    public FormView $filterForm;

    /**
     * The view of the prototypes of the rules, passed to `render()` the same way as the filter form.
     */
    public FormView $prototypesForm;

    public GridView $gridView;

    /**
     * Rendered inside a card of the page (a tab next to the quick search) instead of a card of its own.
     */
    public bool $embedded = false;

    /**
     * Whether any rule was composed — the panel is then marked as active. Given by the page, which takes it from
     * `DatagridNarrowing::$filterActive`.
     */
    public bool $active = false;
}
