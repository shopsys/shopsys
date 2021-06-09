<?php

declare(strict_types=1);

namespace App\Model\Blog\Article\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;

class BlogArticleIndex extends AbstractIndex
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
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Blog\Article\Elasticsearch\BlogArticleExportRepository $blogArticleExportRepository
     */
    public function __construct(
        Domain $domain,
        BlogArticleExportRepository $blogArticleExportRepository
    ) {
        $this->domain = $domain;
        $this->blogArticleExportRepository = $blogArticleExportRepository;
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
     * @return string
     */
    public static function getName(): string
    {
        return 'blog_article';
    }
}
