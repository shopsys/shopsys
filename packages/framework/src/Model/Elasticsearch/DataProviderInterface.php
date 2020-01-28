<?php

namespace Shopsys\FrameworkBundle\Model\Elasticsearch;

interface DataProviderInterface
{
    public const BATCH_SIZE = 100;

    /**
     * @param int $domainId
     * @return int
     */
    public function getTotalCount(int $domainId): int;

    /**
     * @param int $domainId
     * @param int $lastProcessedId
     * @param array $restrictToIds
     * @return array
     */
    public function getDataForBatch(int $domainId, int $lastProcessedId, array $restrictToIds = []): array;
}
