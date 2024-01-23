<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch;

use Redis;
use Shopsys\FrameworkBundle\Component\Redis\RedisDomainQueueFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleFacade;
use Webmozart\Assert\Assert;

class BlogArticleExportQueueFacade extends RedisDomainQueueFacade
{
    /**
     * @param \Redis $redisQueue
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleFacade $blogArticleFacade
     */
    public function __construct(
        Redis $redisQueue,
        protected readonly BlogArticleFacade $blogArticleFacade,
    ) {
        parent::__construct($redisQueue);
    }

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
     */
    public function addAll(int $domainId): void
    {
        $allArticleIds = $this->blogArticleFacade->getAllIdsByDomainId($domainId);

        $this->addIdsBatch($allArticleIds, $domainId);
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
