<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Request;

use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterFormData;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\DatagridFilterFormType;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Reads the state of a datagrid from the current request — the one place that knows how the state travels
 * in the URL. Called by `DatagridFactory`, so a datagrid gets its state without touching the request.
 */
final class DatagridRequestStateResolver
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function resolve(string $gridId): DatagridRequestState
    {
        $request = $this->requestStack->getMainRequest();

        if ($request === null) {
            return new DatagridRequestState();
        }

        return new DatagridRequestState(
            $this->createQuickSearchForm($gridId, $request),
            fn (FilterCollection $filters): FormInterface => $this->createFilterForm($gridId, $filters, $request),
        );
    }

    /**
     * A filter with no rule composed — never submitted, or submitted empty — is shown as the starting point,
     * one group with one rule, so the administrator never faces an empty panel.
     */
    private function createFilterForm(string $gridId, FilterCollection $filters, Request $request): FormInterface
    {
        $form = $this->submitFromQuery($this->createFilterFormWithData($gridId, $filters, new FilterFormData()), $request);

        if ($form->isSubmitted() && $form->getData()->hasRules()) {
            return $form;
        }

        return $this->createFilterFormWithData($gridId, $filters, FilterFormData::createStartingPoint());
    }

    private function createFilterFormWithData(
        string $gridId,
        FilterCollection $filters,
        FilterFormData $data,
    ): FormInterface {
        return $this->formFactory->createNamed(
            DatagridRequestState::createFilterFormName($gridId),
            DatagridFilterFormType::class,
            $data,
            ['filters' => $filters],
        );
    }

    /**
     * The quick search form of the administration, only named per datagrid. A GET form is submitted from the
     * query by hand — `handleRequest()` ignores a GET form during a POST request (symfony/symfony#12244),
     * which is what happens when a live component on the page re-renders.
     */
    private function createQuickSearchForm(string $gridId, Request $request): FormInterface
    {
        $form = $this->formFactory->createNamed(
            DatagridRequestState::createQuickSearchFormName($gridId),
            QuickSearchFormType::class,
            new QuickSearchFormData(),
        );

        return $this->submitFromQuery($form, $request);
    }

    private function submitFromQuery(FormInterface $form, Request $request): FormInterface
    {
        if ($request->query->has($form->getName())) {
            $form->submit($request->query->all($form->getName()));
        }

        return $form;
    }
}
