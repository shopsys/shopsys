<?php

declare(strict_types=1);

namespace App\Model\Article\Elasticsearch;

use App\Model\Article\Article;
use App\Model\Article\ArticleRepository;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class ArticleExportRepository
{
    /**
     * @var \App\Model\Article\ArticleRepository
     */
    private ArticleRepository $articleRepository;

    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @param \App\Model\Article\ArticleRepository $articleRepository
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        ArticleRepository $articleRepository,
        FriendlyUrlFacade $friendlyUrlFacade
    ) {
        $this->articleRepository = $articleRepository;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
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
        return [
            'name' => $article->getName(),
            'text' => $article->getText(),
            'url' => $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityId(
                $article->getDomainId(),
                'front_article_detail',
                $article->getId()
            ),
        ];
    }
}
