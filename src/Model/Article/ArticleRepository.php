<?php

declare(strict_types=1);

namespace App\Model\Article;

use Shopsys\FrameworkBundle\Model\Article\ArticleRepository as BaseArticleRepository;

/**
 * @method \App\Model\Article\Article|null findById(int $articleId)
 * @method \App\Model\Article\Article[] getVisibleArticlesForPlacement(int $domainId, string $placement)
 * @method \App\Model\Article\Article[] getVisibleListByDomainIdAndPlacement(int $domainId, string $placement, int $limit, int $offset)
 * @method \App\Model\Article\Article getById(int $articleId)
 * @method \App\Model\Article\Article getVisibleById(int $articleId)
 * @method \App\Model\Article\Article[] getVisibleListByDomainId(int $domainId, int $limit, int $offset)
 * @method \App\Model\Article\Article[] getAllByDomainId(int $domainId)
 * @method \App\Model\Article\Article getVisibleByDomainIdAndUuid(int $domainId, string $uuid)
 */
class ArticleRepository extends BaseArticleRepository
{
    /**
     * @param int $domainId
     * @param string[] $placements
     * @return \App\Model\Article\Article[]
     */
    public function getVisibleArticlesForPlacements(int $domainId, array $placements): array
    {
        if (count($placements) === 0) {
            return [];
        }
        $queryBuilder = $this->getVisibleArticlesByDomainIdQueryBuilder($domainId)
            ->andWhere('a.placement IN (:placements)')->setParameter('placements', $placements)
            ->orderBy('a.position, a.id');

        return $queryBuilder->getQuery()->execute();
    }
}
