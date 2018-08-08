<?php

namespace Shopsys\FrameworkBundle\Component\Grid\InlineEdit;

use Symfony\Component\HttpFoundation\Request;

interface GridInlineEditInterface
{
    /**
     * @param mixed $rowId
     */
    public function getForm($rowId): \Symfony\Component\Form\FormInterface;

    /**
     * @param mixed $rowId
     * @return mixed
     */
    public function saveForm(Request $request, $rowId);

    public function getGrid(): \Shopsys\FrameworkBundle\Component\Grid\Grid;

    public function canAddNewRow(): bool;

    public function getServiceName(): string;
}
