<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Search\TwigComponent;

use Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearch;
use Shopsys\AdministrationBundle\Component\Datagrid\Url\DatagridNarrowingUrlFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Symfony\Component\Form\FormView;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * Renders the quick search of a datagrid — a GET form named after the datagrid, carrying the order and the
 * limit of the grid along so that a search never loses them, and starting from the first page.
 */
#[AsTwigComponent(
    name: 'Admin:Grid:QuickSearch',
    template: '@ShopsysAdministration/components/grid/quick_search.html.twig',
)]
final class QuickSearchComponent
{
    public QuickSearch $quickSearch;

    public GridView $gridView;

    /**
     * Rendered inside the toolbar of the list instead of a card of its own.
     */
    public bool $embedded = false;

    private ?FormView $formView = null;

    public function __construct(
        private readonly DatagridNarrowingUrlFactory $narrowingUrlFactory,
    ) {
    }

    public function getFormView(): FormView
    {
        return $this->formView ??= $this->quickSearch->form->createView();
    }

    /**
     * The listing with the searched text dropped — offered as a cross in the input while something is searched for.
     */
    public function getResetUrl(): string
    {
        return $this->narrowingUrlFactory->createUrlWithout([$this->quickSearch->form->getName()]);
    }
}
