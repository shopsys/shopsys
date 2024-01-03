<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbFacade;
use Shopsys\FrameworkBundle\Component\GrapesJs\GrapesJsParser;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleRepository;

class ArticleExportRepository
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleRepository $articleRepository
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbFacade $breadcrumbFacade
     * @param \Shopsys\FrameworkBundle\Component\GrapesJs\GrapesJsParser $grapesJsParser
     */
    public function __construct(
        protected readonly ArticleRepository $articleRepository,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly BreadcrumbFacade $breadcrumbFacade,
        protected readonly GrapesJsParser $grapesJsParser,
    ) {
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
     * @return \Shopsys\FrameworkBundle\Model\Article\Article[]
     */
    public function getAllVisibleArticleSitesByDomainId(int $domainId, int $limit, int $lastProcessedId): array
    {
        return $this->articleRepository->getVisibleArticlesByDomainIdQueryBuilder($domainId)
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
     * @return \Shopsys\FrameworkBundle\Model\Article\Article[]
     */
    public function getVisibleArticleSitesByDomainIdAndArticleIds(int $domainId, array $articleIds): array
    {
        return $this->articleRepository->getVisibleArticlesByDomainIdQueryBuilder($domainId)
            ->andWhere('a.id IN (:articleIds)')
            ->setParameter('articleIds', $articleIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\Article $article
     * @return array
     */
    public function extractArticle(Article $article): array
    {
        $domainId = $article->getDomainId();
        $articleId = $article->getId();
        $mainFriendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl($domainId, 'front_article_detail', $articleId);

        if ($article->isLinkType()) {
            $url = $article->getUrl();
        } else {
            $url = $this->friendlyUrlFacade->getAbsoluteUrlByFriendlyUrl($mainFriendlyUrl);
        }

        return [
            'name' => $article->getName(),
            'text' => $this->grapesJsParser->parse($article->getText()),
            'url' => $url,
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
            'type' => $article->getType(),
        ];
    }
}
