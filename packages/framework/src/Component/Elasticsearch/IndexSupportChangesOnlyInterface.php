<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

interface IndexSupportChangesOnlyInterface
{
    public function getChangedCount(int $domainId): int;

    /**
     * @return int[]
     */
    public function getChangedIdsForBatch(int $domainId, int $lastProcessedId, int $batchSize): array;
}
