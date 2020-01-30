<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

abstract class AbstractExportScheduler
{
    protected $rowId = [];

    /**
     * @param int $rowId
     */
    public function scheduleRowIdForImmediateExport(int $rowId): void
    {
        $this->rowId[] = $rowId;
    }

    /**
     * @return bool
     */
    public function hasAnyRowIdsForImmediateExport(): bool
    {
        return $this->rowId !== [];
    }

    /**
     * @return int[]
     */
    public function getRowIdsForImmediateExport(): array
    {
        return array_unique($this->rowId);
    }
}
