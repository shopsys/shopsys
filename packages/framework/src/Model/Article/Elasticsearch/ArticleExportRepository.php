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
    public function __construct(
        protected readonly ArticleRepository $articleRepository,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly BreadcrumbFacade $breadcrumbFacade,
        protected readonly GrapesJsParser $grapesJsParser,
    ) {
    }

    public function getVisibleArticleSitesCountByDomainId(int $domainId): int
    {
        return (int)($this->articleRepository->getVisibleArticlesByDomainIdQueryBuilder($domainId)
            ->select('COUNT(a)')
            ->andWhere('a.type = :type')
            ->setParameter('type', Article::TYPE_SITE)
            ->getQuery()->getSingleScalarResult());
    }

    /**
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
     * @param array<int, int> $articleIds
     * @return array<int, \Shopsys\FrameworkBundle\Model\Article\Article>
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
     * @return array<string, mixed>
     */
    public function extractArticle(Article $article): array
    {
        $domainId = $article->getDomainId();
        $articleId = $article->getId();
        $mainFriendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl($domainId, 'front_article_detail', $articleId);

        return [
            'name' => $article->getName(),
            'text' => $this->grapesJsParser->parse($article->getText()),
            'url' => $article->getUrl(),
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
