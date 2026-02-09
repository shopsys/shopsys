<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Elasticsearch\__fixtures;

use Override;
use RuntimeException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;

class CategoryIndex extends AbstractIndex
{
    #[Override]
    public static function getName(): string
    {
        return 'category';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTotalCount(int $domainId): int
    {
        throw new RuntimeException(sprintf('The %s() is not implemented.', __METHOD__));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getExportDataForIds(int $domainId, array $restrictToIds, array $fields = []): array
    {
        throw new RuntimeException(sprintf('The %s() is not implemented.', __METHOD__));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getExportDataForBatch(
        int $domainId,
        int $lastProcessedId,
        int $batchSize,
        array $fields = [],
    ): array {
        throw new RuntimeException(sprintf('The %s() is not implemented.', __METHOD__));
    }
}
