<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

abstract class AbstractIndex
{
    public const BATCH_SIZE = 100;

    abstract public static function getName(): string;

    abstract public function getTotalCount(int $domainId): int;

    /**
     * @param int[] $restrictToIds
     * @param string[] $fields
     * @return array<int, array<string, mixed>>
     */
    abstract public function getExportDataForIds(int $domainId, array $restrictToIds, array $fields = []): array;

    /**
     * @param string[] $fields
     * @return array<int, array<string, mixed>>
     */
    abstract public function getExportDataForBatch(
        int $domainId,
        int $lastProcessedId,
        int $batchSize,
        array $fields = [],
    ): array;

    public function getExportBatchSize(): int
    {
        return static::BATCH_SIZE;
    }
}
