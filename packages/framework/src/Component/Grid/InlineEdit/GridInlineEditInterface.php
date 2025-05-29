<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid\InlineEdit;

use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

interface GridInlineEditInterface
{
    /**
     * @param int|string|null $rowId
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm(int|string|null $rowId): FormInterface;

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int|string|null $rowId
     * @return int|string
     */
    public function saveForm(Request $request, int|string|null $rowId): int|string;

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function getGrid(): Grid;

    /**
     * @return bool
     */
    public function canAddNewRow(): bool;

    /**
     * @return string
     */
    public function getServiceName(): string;
}
