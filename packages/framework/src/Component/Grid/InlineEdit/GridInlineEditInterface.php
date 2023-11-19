<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid\InlineEdit;

use Symfony\Component\HttpFoundation\Request;

interface GridInlineEditInterface
{
    /**
     * @param mixed $rowId
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm($rowId): \Symfony\Component\Form\FormInterface;

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed $rowId
     * @return mixed
     */
    public function saveForm(Request $request, $rowId);

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function getGrid(): \Shopsys\FrameworkBundle\Component\Grid\Grid;

    /**
     * @return bool
     */
    public function canAddNewRow(): bool;

    /**
     * @return string
     */
    public function getServiceName(): string;
}
