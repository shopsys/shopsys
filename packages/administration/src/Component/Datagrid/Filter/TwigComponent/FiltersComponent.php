<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter\TwigComponent;

use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterFormData;
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
     * The submitted filter form; a `FormInterface` given to the component arrives as its view.
     */
    public FormView $filterForm;

    public GridView $gridView;

    /**
     * Whether any rule was composed — the panel is then marked as active.
     */
    public function isActive(): bool
    {
        $data = $this->filterForm->vars['data'] ?? null;

        return ($this->filterForm->vars['submitted'] ?? false) === true && $data instanceof FilterFormData && $data->hasRules();
    }
}
