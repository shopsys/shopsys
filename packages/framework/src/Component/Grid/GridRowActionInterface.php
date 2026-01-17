<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

interface GridRowActionInterface
{
    public function getName(): string;

    public function renderData(): ?array;

    public function validate(mixed $data): bool;
}
