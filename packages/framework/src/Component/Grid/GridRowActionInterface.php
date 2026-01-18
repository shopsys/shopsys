<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

interface GridRowActionInterface
{
    public function getName(): string;

    /**
     * @return array{template: string, parameters: array<string, mixed>}|null
     */
    public function renderData(): ?array;

    public function validate(mixed $data): bool;
}
