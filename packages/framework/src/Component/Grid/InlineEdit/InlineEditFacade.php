<?php

namespace Shopsys\FrameworkBundle\Component\Grid\InlineEdit;

use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template T of array<string, mixed>
 */
class InlineEditFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditRegistry<T>
     */
    protected $gridInlineEditRegistry;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditRegistry<T> $gridInlineEditRegistry
     */
    public function __construct(GridInlineEditRegistry $gridInlineEditRegistry)
    {
        $this->gridInlineEditRegistry = $gridInlineEditRegistry;
    }

    /**
     * @param string $serviceName
     * @param int|string|null $rowId
     * @return string
     */
    public function getRenderedFormRow(string $serviceName, int|string|null $rowId): string
    {
        $gridInlineEdit = $this->gridInlineEditRegistry->getGridInlineEdit($serviceName);
        /** @var \Symfony\Component\Form\Form $form */
        $form = $gridInlineEdit->getForm($rowId);

        return $this->renderFormAsRow($gridInlineEdit, $rowId, $form);
    }

    /**
     * @param string $serviceName
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string|int $rowId
     * @return mixed
     */
    public function saveFormData(string $serviceName, Request $request, string|int $rowId): mixed
    {
        $gridInlineEdit = $this->gridInlineEditRegistry->getGridInlineEdit($serviceName);
        return $gridInlineEdit->saveForm($request, $rowId);
    }

    /**
     * @param string $serviceName
     * @param int|string|null $rowId
     * @return string|null
     */
    public function getRenderedRowHtml(string $serviceName, int|string|null $rowId): ?string
    {
        $gridInlineEdit = $this->gridInlineEditRegistry->getGridInlineEdit($serviceName);
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

    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface<T> $gridInlineEditService
     * @param int|string|null $rowId
     * @param \Symfony\Component\Form\Form $form
     * @return string
     */
    protected function renderFormAsRow(GridInlineEditInterface $gridInlineEditService, int|string|null $rowId, Form $form): string
    {
        $grid = $gridInlineEditService->getGrid();
        if ($rowId === null) {
            $gridView = $grid->createViewWithoutRows();
        } else {
            $gridView = $grid->createViewWithOneRow($rowId);
        }

        return $gridView->renderBlock('grid_row', $this->getFormRowTemplateParameters($grid, $form), false);
    }

    /**
     * @template GridRowType as array<string, mixed>
     * @param \Shopsys\FrameworkBundle\Component\Grid\Grid<GridRowType> $grid
     * @param \Symfony\Component\Form\Form $form
     * @return array{
     *     loopIndex: int,
     *     lastRow: bool,
     *     row: GridRowType,
     *     form: \Symfony\Component\Form\FormView
     * }
     */
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
