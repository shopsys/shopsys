<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;

class ArticleIndex extends AbstractIndex
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleExportRepository $articleExportRepository
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ArticleExportRepository $articleExportRepository,
    ) {
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getTotalCount(int $domainId): int
    {
        return $this->articleExportRepository->getVisibleArticleSitesCountByDomainId($domainId);
    }

    /**
     * @param int $domainId
     * @param int $lastProcessedId
     * @param int $batchSize
     * @return array
     */
    public function getExportDataForBatch(int $domainId, int $lastProcessedId, int $batchSize): array
    {
        $results = [];

        foreach ($this->articleExportRepository->getAllVisibleArticleSitesByDomainId($domainId, $batchSize, $lastProcessedId) as $article) {
            $results[$article->getId()] = $this->articleExportRepository->extractArticle($article);
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
        $results = [];

        foreach ($this->articleExportRepository->getVisibleArticleSitesByDomainIdAndArticleIds($domainId, $restrictToIds) as $article) {
            $results[$article->getId()] = $this->articleExportRepository->extractArticle($article);
        }

        return $results;
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'article';
    }
}
