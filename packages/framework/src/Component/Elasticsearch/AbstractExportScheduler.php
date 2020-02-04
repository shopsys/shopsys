<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

abstract class AbstractExportScheduler
{
    protected $rowIds = [];

    /**
     * @param int $rowId
     */
    public function scheduleRowIdForImmediateExport(int $rowId): void
    {
        $this->rowIds[] = $rowId;
    }

    /**
     * @return bool
     */
    public function hasAnyRowIdsForImmediateExport(): bool
    {
        return $this->rowIds !== [];
    }

    /**
     * @return int[]
     */
    public function getRowIdsForImmediateExport(): array
    {
        return array_unique($this->rowIds);
    }
}
