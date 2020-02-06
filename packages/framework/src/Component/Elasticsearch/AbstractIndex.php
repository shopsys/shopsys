<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

abstract class AbstractIndex
{
    public const BATCH_SIZE = 100;

    /**
     * @return string
     */
    abstract public function getName(): string;

    /**
     * @param int $domainId
     * @return int
     */
    abstract public function getTotalCount(int $domainId): int;

    /**
     * @param int $domainId
     * @param array $restrictToIds
     * @return array
     */
    abstract public function getExportDataForIds(int $domainId, array $restrictToIds): array;

    /**
     * @param int $domainId
     * @param int $lastProcessedId
     * @param int $batchSize
     * @return array
     */
    abstract public function getExportDataForBatch(int $domainId, int $lastProcessedId, int $batchSize): array;

    /**
     * @return int
     */
    public function getExportBatchSize(): int
    {
        return static::BATCH_SIZE;
    }

    /**
     * @param int $domainId
     * @param int $lastProcessedId
     * @param array $restrictToIds
     * @return array
     */
    public function getExportData(int $domainId, int $lastProcessedId, array $restrictToIds = []): array
    {
        if (!empty($restrictToIds)) {
            return $this->getExportDataForIds($domainId, $restrictToIds);
        }

        return $this->getExportDataForBatch($domainId, $lastProcessedId, $this->getExportBatchSize());
    }
}
