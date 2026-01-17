<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch;

use Redis;
use Shopsys\FrameworkBundle\Component\Redis\RedisDomainQueueFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleFacade;
use Webmozart\Assert\Assert;

class BlogArticleExportQueueFacade extends RedisDomainQueueFacade
{
    public function __construct(
        Redis $redisQueue,
        protected readonly BlogArticleFacade $blogArticleFacade,
    ) {
        parent::__construct($redisQueue);
    }

    /**
     * @param int[] $ids
     */
    public function addIdsBatch(array $ids, int $domainId): void
    {
        Assert::allInteger($ids);

        $this->addBatch($ids, $domainId);
    }

    public function addAll(int $domainId): void
    {
        $allArticleIds = $this->blogArticleFacade->getAllIdsByDomainId($domainId);

        $this->addIdsBatch($allArticleIds, $domainId);
    }

    /**
     * @return int[]
     */
    public function getIds(int $domainId, int $batchSize): array
    {
        return $this->getValues($domainId, $batchSize);
    }
}
