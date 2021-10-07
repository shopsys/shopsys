<?php

declare(strict_types=1);

namespace App\Model\Blog\Article\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexSupportChangesOnlyInterface;

class BlogArticleIndex extends AbstractIndex implements IndexSupportChangesOnlyInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected Domain $domain;

    /**
     * @var \App\Model\Blog\Article\Elasticsearch\BlogArticleExportRepository
     */
    private BlogArticleExportRepository $blogArticleExportRepository;

    /**
     * @var \App\Model\Blog\Article\Elasticsearch\BlogArticleExportQueueFacade
     */
    private BlogArticleExportQueueFacade $blogArticleExportQueueFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Blog\Article\Elasticsearch\BlogArticleExportRepository $blogArticleExportRepository
     * @param \App\Model\Blog\Article\Elasticsearch\BlogArticleExportQueueFacade $blogArticleExportQueueFacade
     */
    public function __construct(
        Domain $domain,
        BlogArticleExportRepository $blogArticleExportRepository,
        BlogArticleExportQueueFacade $blogArticleExportQueueFacade
    ) {
        $this->domain = $domain;
        $this->blogArticleExportRepository = $blogArticleExportRepository;
        $this->blogArticleExportQueueFacade = $blogArticleExportQueueFacade;
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getTotalCount(int $domainId): int
    {
        return $this->blogArticleExportRepository->getVisibleBlogArticlesCountByDomainIdAndLocale(
            $domainId,
            $this->domain->getDomainConfigById($domainId)->getLocale()
        );
    }

    /**
     * @param int $domainId
     * @param int $lastProcessedId
     * @param int $batchSize
     * @return array
     */
    public function getExportDataForBatch(int $domainId, int $lastProcessedId, int $batchSize): array
    {
        $locale = $this->domain->getDomainConfigById($domainId)->getLocale();

        $results = [];
        foreach ($this->blogArticleExportRepository->getVisibleBlogArticlesByDomainIdAndLocale($domainId, $locale, $batchSize, $lastProcessedId) as $blogArticle) {
            $results[$blogArticle->getId()] = $this->blogArticleExportRepository->extractBlogArticle($blogArticle, $domainId, $locale);
        }

        return $results;
    }

    /**
     * @param int $domainId
     * @param array $restrictToIds
     * @return array
     */
    public function getExportDataForIds(int $domainId, array $restrictToIds): array
    {
        $locale = $this->domain->getDomainConfigById($domainId)->getLocale();

        $results = [];
        foreach ($this->blogArticleExportRepository->getVisibleBlogArticlesByDomainIdAndLocaleAndBlogArticleIds($domainId, $locale, $restrictToIds) as $blogArticle) {
            $results[$blogArticle->getId()] = $this->blogArticleExportRepository->extractBlogArticle($blogArticle, $domainId, $locale);
        }

        return $results;
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getChangedCount(int $domainId): int
    {
        return $this->blogArticleExportQueueFacade->getCount($domainId);
    }

    /**
     * @param int $domainId
     * @param int $lastProcessedId
     * @param int $batchSize
     * @return int[]
     */
    public function getChangedIdsForBatch(int $domainId, int $lastProcessedId, int $batchSize): array
    {
        return $this->blogArticleExportQueueFacade->getIds($domainId, $batchSize);
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'blog_article';
    }
}
