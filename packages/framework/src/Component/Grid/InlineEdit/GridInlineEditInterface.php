<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid\InlineEdit;

use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

interface GridInlineEditInterface
{
    public function getForm(int|string|null $rowId): FormInterface;

    public function saveForm(Request $request, int|string|null $rowId): int|string;

    public function getGrid(): Grid;

    public function canAddNewRow(): bool;

    public function getServiceName(): string;
}
