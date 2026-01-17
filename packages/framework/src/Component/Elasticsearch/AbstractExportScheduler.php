<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Override;
use Symfony\Contracts\Service\ResetInterface;

abstract class AbstractExportScheduler implements ResetInterface
{
    /**
     * @var int[]
     */
    protected array $rowIds = [];

    public function scheduleRowIdForImmediateExport(int $rowId): void
    {
        $this->rowIds[] = $rowId;
    }

    /**
     * @param int[] $rowIds
     */
    public function scheduleRowIdsForImmediateExport(array $rowIds): void
    {
        $this->rowIds = array_merge($this->rowIds, $rowIds);
    }

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

    #[Override]
    public function reset(): void
    {
        $this->rowIds = [];
    }
}
