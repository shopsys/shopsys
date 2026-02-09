<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\UnsupportedFeatureException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexSupportChangesOnlyInterface;

class BlogArticleIndex extends AbstractIndex implements IndexSupportChangesOnlyInterface
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly BlogArticleExportRepository $blogArticleExportRepository,
        protected readonly BlogArticleExportQueueFacade $blogArticleExportQueueFacade,
    ) {
    }

    #[Override]
    public function getTotalCount(int $domainId): int
    {
        return $this->blogArticleExportRepository->getVisibleBlogArticlesCountByDomainIdAndLocale(
            $domainId,
            $this->domain->getDomainConfigById($domainId)->getLocale(),
        );
    }

    /**
     * @param string[] $fields
     */
    #[Override]
    public function getExportDataForBatch(
        int $domainId,
        int $lastProcessedId,
        int $batchSize,
        array $fields = [],
    ): array {
        if ($fields !== []) {
            throw new UnsupportedFeatureException('Scoping export by fields is not supported for blog articles.');
        }
        $locale = $this->domain->getDomainConfigById($domainId)->getLocale();

        $results = [];

        foreach ($this->blogArticleExportRepository->getVisibleBlogArticlesByDomainIdAndLocale($domainId, $locale, $batchSize, $lastProcessedId) as $blogArticle) {
            $results[$blogArticle->getId()] = $this->blogArticleExportRepository->extractBlogArticle($blogArticle, $domainId, $locale);
        }

        return $results;
    }

    /**
     * @param string[] $fields
     */
    #[Override]
    public function getExportDataForIds(int $domainId, array $restrictToIds, array $fields = []): array
    {
        if ($fields !== []) {
            throw new UnsupportedFeatureException('Scoping export by fields is not supported for blog articles.');
        }
        $locale = $this->domain->getDomainConfigById($domainId)->getLocale();

        $results = [];

        foreach ($this->blogArticleExportRepository->getVisibleBlogArticlesByDomainIdAndLocaleAndBlogArticleIds($domainId, $locale, $restrictToIds) as $blogArticle) {
            $results[$blogArticle->getId()] = $this->blogArticleExportRepository->extractBlogArticle($blogArticle, $domainId, $locale);
        }

        return $results;
    }

    #[Override]
    public function getChangedCount(int $domainId): int
    {
        return $this->blogArticleExportQueueFacade->getCount($domainId);
    }

    /**
     * @return int[]
     */
    #[Override]
    public function getChangedIdsForBatch(int $domainId, int $lastProcessedId, int $batchSize): array
    {
        return $this->blogArticleExportQueueFacade->getIds($domainId, $batchSize);
    }

    #[Override]
    public static function getName(): string
    {
        return 'blog_article';
    }
}
