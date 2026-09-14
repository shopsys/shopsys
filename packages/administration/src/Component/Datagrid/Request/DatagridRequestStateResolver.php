<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Request;

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

        return new DatagridRequestState($this->createQuickSearchForm($gridId, $request));
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
