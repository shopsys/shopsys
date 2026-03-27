<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

interface GridRowActionInterface
{
    public function getName(): string;

    public function build(mixed $data): ?array;
}
