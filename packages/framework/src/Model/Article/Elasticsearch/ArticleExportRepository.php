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

    public function getVisibleArticlesCountByDomainId(int $domainId): int
    {
        return (int)($this->articleRepository->getVisibleArticlesByDomainIdQueryBuilder($domainId)
            ->select('COUNT(a)')
            ->getQuery()->getSingleScalarResult());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Article\Article[]
     */
    public function getAllVisibleArticlesByDomainId(int $domainId, int $limit, int $lastProcessedId): array
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
     * @param int[] $articleIds
     * @return \Shopsys\FrameworkBundle\Model\Article\Article[]
     */
    public function getVisibleArticlesByDomainIdAndArticleIds(int $domainId, array $articleIds): array
    {
        return $this->articleRepository->getVisibleArticlesByDomainIdQueryBuilder($domainId)
            ->andWhere('a.id IN (:articleIds)')
            ->setParameter('articleIds', $articleIds)
            ->getQuery()
            ->getResult();
    }

    public function extractArticle(Article $article): array
    {
        $domainId = $article->getDomainId();
        $articleId = $article->getId();

        $extractedArticle = [
            'name' => $article->getName(),
            'text' => $this->grapesJsParser->parse($article->getText()),
            'url' => $article->getUrl(),
            'uuid' => $article->getUuid(),
            'placement' => $article->getPlacement(),
            'seoH1' => $article->getSeoH1(),
            'seoTitle' => $article->getSeoTitle(),
            'seoMetaDescription' => $article->getSeoMetaDescription(),
            'position' => $article->getPosition(),
            'external' => $article->isExternal(),
            'createdAt' => $article->getCreatedAt()->format('Y-m-d H:i:s'),
            'type' => $article->getType(),
        ];

        // a link type article is indexed only to be listed (e.g. in the footer) and has no friendly URL to derive the fields below from;
        // the empty values also clear them in a document of an article that has just been switched from the site type
        if ($article->isLinkType()) {
            return array_merge($extractedArticle, [
                'slug' => [],
                'mainSlug' => null,
                'breadcrumb' => [],
            ]);
        }

        $mainFriendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl($domainId, 'front_article_detail', $articleId);

        return array_merge($extractedArticle, [
            'slug' => $this->friendlyUrlFacade->getAllSlugsByRouteNameAndEntityId($domainId, 'front_article_detail', $articleId),
            'mainSlug' => $mainFriendlyUrl->getSlug(),
            'breadcrumb' => $this->breadcrumbFacade->getBreadcrumbOnDomain($articleId, 'front_article_detail', $domainId),
        ]);
    }
}
