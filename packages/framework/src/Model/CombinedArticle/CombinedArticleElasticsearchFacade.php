<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CombinedArticle;

class CombinedArticleElasticsearchFacade
{
    public function __construct(
        protected readonly CombinedArticleElasticsearchRepository $combinedArticleElasticsearchRepository,
    ) {
    }

    public function getArticlesBySearchText(string $searchText, int $domainId, ?int $limit = null): array
    {
        return $this->combinedArticleElasticsearchRepository->getArticlesBySearchText($searchText, $domainId, $limit);
    }

    public function getArticlesByDomainId(int $domainId, int $from, int $limit): array
    {
        return $this->combinedArticleElasticsearchRepository->getArticlesByDomainId($domainId, $from, $limit);
    }

    /**
     * @param array<string, array<int, string>> $idsByType
     */
    public function getArticlesByIds(array $idsByType, int $domainId, int $limit = 50): array
    {
        return $this->combinedArticleElasticsearchRepository->getArticlesByIds($idsByType, $domainId, $limit);
    }
}
