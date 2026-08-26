<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article\Elasticsearch;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\UnsupportedFeatureException;

class ArticleIndex extends AbstractIndex
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ArticleExportRepository $articleExportRepository,
    ) {
    }

    #[Override]
    public function getTotalCount(int $domainId): int
    {
        return $this->articleExportRepository->getVisibleArticlesCountByDomainId($domainId);
    }

    /**
     * @param string[] $fields
     */
    #[Override]
    public function getExportDataForBatch(
        int $domainId,
        int $lastProcessedId,
        int $batchSize,
        array $fields = self::ALL_FIELDS,
    ): array {
        if ($fields !== self::ALL_FIELDS) {
            throw new UnsupportedFeatureException('Scoping export by fields is not supported for articles.');
        }
        $results = [];

        foreach ($this->articleExportRepository->getAllVisibleArticlesByDomainId($domainId, $batchSize, $lastProcessedId) as $article) {
            $results[$article->getId()] = $this->articleExportRepository->extractArticle($article);
        }

        return $results;
    }

    /**
     * @param string[] $fields
     */
    #[Override]
    public function getExportDataForIds(
        int $domainId,
        array $restrictToIds,
        array $fields = self::ALL_FIELDS,
    ): array {
        if ($fields !== self::ALL_FIELDS) {
            throw new UnsupportedFeatureException('Scoping export by fields is not supported for articles.');
        }
        $results = [];

        foreach ($this->articleExportRepository->getVisibleArticlesByDomainIdAndArticleIds($domainId, $restrictToIds) as $article) {
            $results[$article->getId()] = $this->articleExportRepository->extractArticle($article);
        }

        return $results;
    }

    #[Override]
    public static function getName(): string
    {
        return 'article';
    }
}
