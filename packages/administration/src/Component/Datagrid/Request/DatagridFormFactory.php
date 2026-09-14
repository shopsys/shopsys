<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Request;

use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Builds the GET forms of a datagrid from its request state.
 *
 * A GET form is submitted from the query by hand — `handleRequest()` ignores a GET form during a POST
 * request (symfony/symfony#12244), which is what happens when a live component on the page re-renders.
 */
class DatagridFormFactory
{
    public function __construct(
        protected readonly FormFactoryInterface $formFactory,
    ) {
    }

    /**
     * The quick search form of the administration, only named per datagrid.
     */
    public function createQuickSearchForm(DatagridRequestState $state): FormInterface
    {
        $form = $this->formFactory->createNamed(
            $state->getQuickSearchFormName(),
            QuickSearchFormType::class,
            new QuickSearchFormData(),
        );

        if ($state->quickSearchQuery !== null) {
            $form->submit($state->quickSearchQuery);
        }

        return $form;
    }

    /**
     * The searched text of a quick search form, null when nothing was searched for.
     */
    public function getSearchTerm(FormInterface $quickSearchForm): ?string
    {
        $data = $quickSearchForm->getData();

        if ($data instanceof QuickSearchFormData === false || $data->text === null) {
            return null;
        }

        $term = trim($data->text);

        return $term === '' ? null : $term;
    }
}
