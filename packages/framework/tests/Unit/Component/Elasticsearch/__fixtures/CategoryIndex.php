<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Elasticsearch\__fixtures;

use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

class CategoryIndex extends AbstractIndex
{
    public const INDEX_NAME = 'category';

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::INDEX_NAME;
    }

    /**
     * @inheritDoc
     */
    public function getTotalCount(int $domainId): int
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    /**
     * @inheritDoc
     */
    public function getExportDataForIds(int $domainId, array $restrictToIds): array
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    /**
     * @inheritDoc
     */
    public function getExportDataForBatch(int $domainId, int $lastProcessedId, int $batchSize): array
    {
        throw new MethodNotImplementedException(__METHOD__);
    }
}
