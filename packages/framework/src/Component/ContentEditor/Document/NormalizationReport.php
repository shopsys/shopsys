<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Document;

class NormalizationReport
{
    /**
     * @var string[]
     */
    protected array $droppedBlockTypes = [];

    /**
     * @var string[]
     */
    protected array $flattenedContainerTypes = [];

    public function addDroppedBlockType(string $type): void
    {
        $this->droppedBlockTypes[] = $type;
    }

    public function addFlattenedContainerType(string $type): void
    {
        $this->flattenedContainerTypes[] = $type;
    }

    /**
     * @return string[]
     */
    public function getDroppedBlockTypes(): array
    {
        return $this->droppedBlockTypes;
    }

    /**
     * @return string[]
     */
    public function getFlattenedContainerTypes(): array
    {
        return $this->flattenedContainerTypes;
    }
}
