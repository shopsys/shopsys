<?php

namespace Tests\FrameworkBundle\Unit\Model\Elasticsearch\__fixtures;

use Shopsys\FrameworkBundle\Component\Elasticsearch\DataProviderInterface;
use Symplify\BetterPhpDocParser\Exception\NotImplementedYetException;

class CategoryDataProvider implements DataProviderInterface
{
    /**
     * @param int $domainId
     * @return int
     */
    public function getTotalCount(int $domainId): int
    {
        throw new NotImplementedYetException();
    }

    /**
     * @param int $domainId
     * @param int $lastProcessedId
     * @param array $restrictToIds
     * @return array
     */
    public function getDataForBatch(int $domainId, int $lastProcessedId, array $restrictToIds = []): array
    {
        throw new NotImplementedYetException();
    }
}
