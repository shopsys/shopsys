<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form;

use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterFormData;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Builds the forms of the filter of a datagrid — the only place they are built, so that whoever renders the filter
 * builds them the same way.
 */
class FilterFormFactory
{
    public function __construct(
        protected readonly FormFactoryInterface $formFactory,
    ) {
    }

    /**
     * The form is submitted from the query by hand, see `DatagridStateEvent::getQuery()`. A filter with no rule
     * composed — never submitted, or submitted empty — is shown as the starting point, one group with one rule,
     * so the administrator never faces an empty panel.
     *
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection $filters The filters offered, already adapted to the medium of the datagrid
     * @param array<string, mixed>|null $query The query of the form, null when the request does not carry it
     * @return \Symfony\Component\Form\FormInterface<\Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterFormData>
     */
    public function createForm(FilterCollection $filters, string $formName, ?array $query): FormInterface
    {
        if ($query !== null) {
            $form = $this->createFormWithData($filters, $formName, new FilterFormData());
            $form->submit($query);

            if ($form->getData()->hasRules()) {
                return $form;
            }
        }

        return $this->createFormWithData($filters, $formName, FilterFormData::createStartingPoint());
    }

    /**
     * The prototypes of the rules the page swaps in, named as the filter form and never submitted.
     */
    public function createPrototypesForm(FilterCollection $filters, string $formName): FormInterface
    {
        return $this->formFactory->createNamed(
            $formName,
            FilterPrototypesFormType::class,
            null,
            ['filters' => $filters],
        );
    }

    /**
     * @return \Symfony\Component\Form\FormInterface<\Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterFormData>
     */
    protected function createFormWithData(
        FilterCollection $filters,
        string $formName,
        FilterFormData $data,
    ): FormInterface {
        return $this->formFactory->createNamed(
            $formName,
            DatagridFilterFormType::class,
            $data,
            ['filters' => $filters],
        );
    }
}
