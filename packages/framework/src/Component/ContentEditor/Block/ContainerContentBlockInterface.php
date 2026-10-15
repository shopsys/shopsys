<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block;

interface ContainerContentBlockInterface extends ContentBlockInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function getSlotCount(array $data): int;
}
