<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

interface IndexSupportChangesOnlyInterface
{
    /**
     * @param int $domainId
     * @return int
     */
    public function getChangedCount(int $domainId): int;

    /**
     * @param int $domainId
     * @param int $lastProcessedId
     * @param int $batchSize
     * @return int[]
     */
    public function getChangedIdsForBatch(int $domainId, int $lastProcessedId, int $batchSize): array;
}
