<?php

namespace Shopsys\FrameworkBundle\Component\Grid\InlineEdit;

use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template T of array<string, mixed>
 */
interface GridInlineEditInterface
{
    /**
     * @param mixed $rowId
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm(mixed $rowId): FormInterface;

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed $rowId
     * @return mixed
     */
    public function saveForm(Request $request, mixed $rowId): mixed;

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid<T>
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
