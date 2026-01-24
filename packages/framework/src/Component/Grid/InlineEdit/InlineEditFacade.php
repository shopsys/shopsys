<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid\InlineEdit;

use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class InlineEditFacade
{
    public function __construct(protected readonly GridInlineEditRegistry $gridInlineEditRegistry)
    {
    }

    public function getRenderedFormRow(string $serviceName, mixed $rowId): string
    {
        $gridInlineEdit = $this->gridInlineEditRegistry->getGridInlineEdit($serviceName);
        /** @var \Symfony\Component\Form\Form $form */
        $form = $gridInlineEdit->getForm($rowId);

        return $this->renderFormAsRow($gridInlineEdit, $rowId, $form);
    }

    public function saveFormData(string $serviceName, Request $request, mixed $rowId): mixed
    {
        $gridInlineEdit = $this->gridInlineEditRegistry->getGridInlineEdit($serviceName);

        return $gridInlineEdit->saveForm($request, $rowId);
    }

    public function getRenderedRowHtml(string $serviceName, mixed $rowId): ?string
    {
        $gridInlineEdit = $this->gridInlineEditRegistry->getGridInlineEdit($serviceName);

        /** @var \Shopsys\FrameworkBundle\Component\Grid\Grid $grid */
        $grid = $gridInlineEdit->getGrid();
        $gridView = $grid->createViewWithOneRow($rowId);
        $rows = $grid->getRows();
        $rowData = array_pop($rows);

        return $gridView->renderBlock('grid_row', [
            'loopIndex' => 0,
            'lastRow' => false,
            'row' => $rowData,
        ], false);
    }

    protected function renderFormAsRow(
        GridInlineEditInterface $gridInlineEditService,
        mixed $rowId,
        Form $form,
    ): string {
        $grid = $gridInlineEditService->getGrid();

        if ($rowId === null) {
            $gridView = $grid->createViewWithoutRows();
        } else {
            $gridView = $grid->createViewWithOneRow($rowId);
        }

        return $gridView->renderBlock('grid_row', $this->getFormRowTemplateParameters($grid, $form), false);
    }

    protected function getFormRowTemplateParameters(Grid $grid, Form $form): array
    {
        $formView = $form->createView();
        $rows = $grid->getRows();

        return [
            'loopIndex' => 0,
            'lastRow' => false,
            'row' => array_pop($rows),
            'form' => $formView,
        ];
    }
}
