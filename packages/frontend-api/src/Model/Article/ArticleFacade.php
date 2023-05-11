<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Article;

use Shopsys\FrameworkBundle\Model\Article\Article;

class ArticleFacade
{
    protected ArticleRepository $articleRepository;

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Article\ArticleRepository $articleRepository
     */
    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getAllVisibleArticlesCountByDomainId(int $domainId): int
    {
        return $this->articleRepository->getAllVisibleArticlesCountByDomainId($domainId);
    }

    /**
     * @param int $domainId
     * @param string $placement
     * @return int
     */
    public function getAllVisibleArticlesCountByDomainIdAndPlacement(int $domainId, string $placement): int
    {
        return $this->articleRepository->getAllVisibleArticlesCountByDomainIdAndPlacement($domainId, $placement);
    }

    /**
     * @param int $domainId
     * @param int $limit
     * @param int $offset
     * @return \Shopsys\FrameworkBundle\Model\Article\Article[]
     */
    public function getVisibleArticlesListByDomainId(
        int $domainId,
        int $limit,
        int $offset
    ): array {
        return $this->articleRepository->getVisibleListByDomainId(
            $domainId,
            $limit,
            $offset
        );
    }

    /**
     * @param int $domainId
     * @param string $placement
     * @param int $limit
     * @param int $offset
     * @return \Shopsys\FrameworkBundle\Model\Article\Article[]
     */
    public function getVisibleArticlesListByDomainIdAndPlacement(
        int $domainId,
        string $placement,
        int $limit,
        int $offset
    ): array {
        return $this->articleRepository->getVisibleListByDomainIdAndPlacement(
            $domainId,
            $placement,
            $limit,
            $offset
        );
    }

    /**
     * @param int $domainId
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function getVisibleByDomainIdAndUuid(int $domainId, string $uuid): Article
    {
        return $this->articleRepository->getVisibleByDomainIdAndUuid($domainId, $uuid);
    }

    /**
     * @param int $domainId
     * @param int $articleId
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function getVisibleByDomainIdAndId(int $domainId, int $articleId): Article
    {
        return $this->articleRepository->getVisibleByDomainIdAndId($domainId, $articleId);
    }
}
