<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CombinedArticle;

class CombinedArticleElasticsearchFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\CombinedArticle\CombinedArticleElasticsearchRepository $combinedArticleElasticsearchRepository
     */
    public function __construct(
        protected readonly CombinedArticleElasticsearchRepository $combinedArticleElasticsearchRepository,
    ) {
    }

    /**
     * @param string $searchText
     * @param int $domainId
     * @param int|null $limit
     * @return array
     */
    public function getArticlesBySearchText(string $searchText, int $domainId, ?int $limit = null): array
    {
        return $this->combinedArticleElasticsearchRepository->getArticlesBySearchText($searchText, $domainId, $limit);
    }

    /**
     * @param int $domainId
     * @param int $from
     * @param int $limit
     * @return array
     */
    public function getArticlesByDomainId(int $domainId, int $from, int $limit): array
    {
        return $this->combinedArticleElasticsearchRepository->getArticlesByDomainId($domainId, $from, $limit);
    }

    /**
     * @param array<string, array<int, string>> $idsByType
     * @param int $domainId
     * @param int $limit
     * @return array
     */
    public function getArticlesByIds(array $idsByType, int $domainId, int $limit = 50): array
    {
        return $this->combinedArticleElasticsearchRepository->getArticlesByIds($idsByType, $domainId, $limit);
    }
}
