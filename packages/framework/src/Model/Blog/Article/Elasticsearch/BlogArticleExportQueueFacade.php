<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Redis\RedisDomainQueueFacade;
use Webmozart\Assert\Assert;

class BlogArticleExportQueueFacade extends RedisDomainQueueFacade
{
    /**
     * @param int[] $ids
     * @param int $domainId
     */
    public function addIdsBatch(array $ids, int $domainId): void
    {
        Assert::allInteger($ids);

        $this->addBatch($ids, $domainId);
    }

    /**
     * @param int $domainId
     * @param int $batchSize
     * @return int[]
     */
    public function getIds(int $domainId, int $batchSize): array
    {
        return $this->getValues($domainId, $batchSize);
    }
}
