<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

abstract class AbstractIndex
{
    public const BATCH_SIZE = 100;

    /**
     * @return string
     */
    abstract public static function getName(): string;

    /**
     * @param int $domainId
     * @return int
     */
    abstract public function getTotalCount(int $domainId): int;

    /**
     * @param int $domainId
     * @param array $restrictToIds
     * @param string[] $fields
     * @return array
     */
    abstract public function getExportDataForIds(int $domainId, array $restrictToIds, array $fields = []): array;

    /**
     * @param int $domainId
     * @param int $lastProcessedId
     * @param int $batchSize
     * @param string[] $fields
     * @return array
     */
    abstract public function getExportDataForBatch(
        int $domainId,
        int $lastProcessedId,
        int $batchSize,
        array $fields = [],
    ): array;

    /**
     * @return int
     */
    public function getExportBatchSize(): int
    {
        return static::BATCH_SIZE;
    }
}
