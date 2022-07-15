<?php

declare(strict_types=1);

namespace App\Model\Article\Elasticsearch;

use App\Component\Breadcrumb\BreadcrumbFacade;
use App\Model\Article\Article;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Article\ArticleRepository;

class ArticleExportRepository
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Article\ArticleRepository
     */
    private ArticleRepository $articleRepository;

    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @var \App\Component\Breadcrumb\BreadcrumbFacade
     */
    protected BreadcrumbFacade $breadcrumbFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleRepository $articleRepository
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Component\Breadcrumb\BreadcrumbFacade $breadcrumbFacade
     */
    public function __construct(
        ArticleRepository $articleRepository,
        FriendlyUrlFacade $friendlyUrlFacade,
        BreadcrumbFacade $breadcrumbFacade
    ) {
        $this->articleRepository = $articleRepository;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->breadcrumbFacade = $breadcrumbFacade;
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getVisibleArticleSitesCountByDomainId(int $domainId): int
    {
        return (int)($this->articleRepository->getVisibleArticlesByDomainIdQueryBuilder($domainId)
            ->select('COUNT(a)')
            ->andWhere('a.type = :type')
            ->setParameter('type', Article::TYPE_SITE)
            ->getQuery()->getSingleScalarResult());
    }

    /**
     * @param int $domainId
     * @param int $limit
     * @param int $lastProcessedId
     * @return \App\Model\Article\Article[]
     */
    public function getAllVisibleArticleSitesByDomainId(int $domainId, int $limit, int $lastProcessedId): array
    {
        return $this->articleRepository->getVisibleArticlesByDomainIdQueryBuilder($domainId)
            ->andWhere('a.type = :type')
            ->setParameter('type', Article::TYPE_SITE)
            ->andWhere('a.id > :lastProcessedId')
            ->setParameter('lastProcessedId', $lastProcessedId)
            ->setMaxResults($limit)
            ->orderBy('a.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $domainId
     * @param int[] $articleIds
     * @return \App\Model\Article\Article[]
     */
    public function getVisibleArticleSitesByDomainIdAndArticleIds(int $domainId, array $articleIds): array
    {
        return $this->articleRepository->getVisibleArticlesByDomainIdQueryBuilder($domainId)
            ->andWhere('a.type = :type')
            ->andWhere('a.id IN (:articleIds)')
            ->setParameter('type', Article::TYPE_SITE)
            ->setParameter('articleIds', $articleIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param \App\Model\Article\Article $article
     * @return array
     */
    public function extractArticle(Article $article): array
    {
        $domainId = $article->getDomainId();
        $articleId = $article->getId();
        $mainFriendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl($domainId, 'front_article_detail', $articleId);

        return [
            'name' => $article->getName(),
            'text' => $article->getText(),
            'url' => $this->friendlyUrlFacade->getAbsoluteUrlByFriendlyUrl($mainFriendlyUrl),
            'uuid' => $article->getUuid(),
            'placement' => $article->getPlacement(),
            'seoH1' => $article->getSeoH1(),
            'seoTitle' => $article->getSeoTitle(),
            'seoMetaDescription' => $article->getSeoMetaDescription(),
            'slug' => $this->friendlyUrlFacade->getAllSlugsByRouteNameAndEntityId($domainId, 'front_article_detail', $articleId),
            'mainSlug' => $mainFriendlyUrl->getSlug(),
            'position' => $article->getPosition(),
            'breadcrumb' => $this->breadcrumbFacade->getBreadcrumbOnDomain($articleId, 'front_article_detail', $domainId),
            'external' => $article->isExternal(),
            'createdAt' => $article->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
