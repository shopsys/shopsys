<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

interface GridRowActionInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return array|null
     */
    public function renderData(): ?array;

    /**
     * @param mixed $data
     * @return bool
     */
    public function validate(mixed $data): bool;
}
