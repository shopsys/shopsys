<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form;

use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterFormData;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class FilterFormFactory
{
    public function __construct(
        protected readonly FormFactoryInterface $formFactory,
    ) {
    }

    /**
     * The form is submitted from the query by hand, see `DatagridStateEvent::getQuery()`. A filter with no rule
     * composed is shown as the starting point, so the administrator never faces an empty panel.
     *
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection $filters The filters offered, already resolved against the adapter of the datagrid
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
