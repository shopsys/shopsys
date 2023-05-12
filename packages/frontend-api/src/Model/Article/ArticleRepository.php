<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Article;

use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleRepository as FrameworkArticleRepository;
use Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException;

class ArticleRepository
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleRepository $articleRepository
     */
    public function __construct(protected readonly FrameworkArticleRepository $articleRepository)
    {
    }

    /**
     * @param int $domainId
     * @param string $placement
     * @param int $limit
     * @param int $offset
     * @return \Shopsys\FrameworkBundle\Model\Article\Article[]
     */
    public function getVisibleListByDomainIdAndPlacement(
        int $domainId,
        string $placement,
        int $limit,
        int $offset
    ): array {
        $queryBuilder = $this->articleRepository->getVisibleArticlesByDomainIdAndPlacementSortedByPositionQueryBuilder(
            $domainId,
            $placement
        )
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param int $domainId
     * @param string $placement
     * @return int
     */
    public function getAllVisibleArticlesCountByDomainIdAndPlacement(int $domainId, string $placement): int
    {
        $queryBuilder = $this->articleRepository->getArticlesByDomainIdQueryBuilder($domainId)
            ->select('COUNT(a)')
            ->andWhere('a.hidden = false')
            ->andWhere('a.placement = :placement')
            ->setParameter('placement', $placement);

        return (int)$queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getAllVisibleArticlesCountByDomainId($domainId): int
    {
        $queryBuilder = $this->articleRepository->getArticlesByDomainIdQueryBuilder($domainId)
            ->select('COUNT(a)')
            ->andWhere('a.hidden = false');

        return (int)$queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param int $domainId
     * @param int $limit
     * @param int $offset
     * @return \Shopsys\FrameworkBundle\Model\Article\Article[]
     */
    public function getVisibleListByDomainId(
        int $domainId,
        int $limit,
        int $offset
    ): array {
        $queryBuilder = $this->articleRepository->getAllVisibleQueryBuilder()
            ->andWhere('a.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->orderBy('a.placement')
            ->addOrderBy('a.position')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param int $domainId
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function getVisibleByDomainIdAndUuid(int $domainId, string $uuid): Article
    {
        $article = $this->articleRepository->getAllVisibleQueryBuilder()
            ->andWhere('a.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->andWhere('a.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()->getOneOrNullResult();

        if ($article === null) {
            $message = 'Article with UUID \'' . $uuid . '\' not found.';

            throw new ArticleNotFoundException($message);
        }

        return $article;
    }

    /**
     * @param int $domainId
     * @param int $articleId
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function getVisibleByDomainIdAndId(int $domainId, int $articleId): Article
    {
        $article = $this->articleRepository->getAllVisibleQueryBuilder()
            ->andWhere('a.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->andWhere('a.id = :id')
            ->setParameter('id', $articleId)
            ->getQuery()->getOneOrNullResult();

        if ($article === null) {
            $message = 'Article with ID \'' . $articleId . '\' not found.';

            throw new ArticleNotFoundException($message);
        }

        return $article;
    }
}
