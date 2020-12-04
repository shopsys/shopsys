<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Article;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleRepository as FrameworkArticleRepository;
use Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException;

class ArticleRepository
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Article\ArticleRepository
     */
    protected $articleRepository;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleRepository $articleRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(FrameworkArticleRepository $articleRepository, EntityManagerInterface $em)
    {
        $this->articleRepository = $articleRepository;
        $this->em = $em;
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
        $queryBuilder = $this->getVisibleArticlesByDomainIdAndPlacementSortedByPositionQueryBuilder(
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
     * @return \Doctrine\ORM\QueryBuilder
     * @deprecated This method will be removed in next major version. It will be replaced by \Shopsys\FrameworkBundle\Model\Article\ArticleRepository::getVisibleArticlesByDomainIdAndPlacementSortedByPositionQueryBuilder() which will change its visibility to public.
     */
    protected function getVisibleArticlesByDomainIdAndPlacementSortedByPositionQueryBuilder(
        int $domainId,
        string $placement
    ): QueryBuilder {
        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. It will be replaced by \Shopsys\FrameworkBundle\Model\Article\ArticleRepository::getVisibleArticlesByDomainIdAndPlacementSortedByPositionQueryBuilder() which will change its visibility to public.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        return $this->articleRepository->getVisibleArticlesByDomainIdQueryBuilder($domainId)
            ->andWhere('a.placement = :placement')->setParameter('placement', $placement)
            ->orderBy('a.position, a.id');
    }

    /**
     * @param int $domainId
     * @param string $placement
     * @return int
     */
    public function getAllVisibleArticlesCountByDomainIdAndPlacement(int $domainId, string $placement): int
    {
        $queryBuilder = $this->getArticlesByDomainIdQueryBuilder($domainId)
            ->select('COUNT(a)')
            ->andWhere('a.hidden = false')
            ->andWhere('a.placement = :placement')
            ->setParameter('placement', $placement);

        return (int)$queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     * @deprecated This method will be removed in next major version. It will be replaced by \Shopsys\FrameworkBundle\Model\Article\ArticleRepository::getArticlesByDomainIdQueryBuilder() which will change its visibility to public.
     */
    protected function getArticlesByDomainIdQueryBuilder($domainId)
    {
        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. It will be replaced by \Shopsys\FrameworkBundle\Model\Article\ArticleRepository::getArticlesByDomainIdQueryBuilder() which will change its visibility to public.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        return $this->em->createQueryBuilder()
            ->select('a')
            ->from(Article::class, 'a')
            ->where('a.domainId = :domainId')->setParameter('domainId', $domainId);
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getAllVisibleArticlesCountByDomainId($domainId): int
    {
        $queryBuilder = $this->getArticlesByDomainIdQueryBuilder($domainId)
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
        $queryBuilder = $this->getAllVisibleQueryBuilder()
            ->andWhere('a.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->orderBy('a.placement')
            ->addOrderBy('a.position')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     * @deprecated This method will be removed in next major version. It will be replaced by \Shopsys\FrameworkBundle\Model\Article\ArticleRepository::getAllVisibleQueryBuilder() which will change its visibility to public.
     */
    protected function getAllVisibleQueryBuilder()
    {
        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. It will be replaced by \Shopsys\FrameworkBundle\Model\Article\ArticleRepository::getAllVisibleQueryBuilder() which will change its visibility to public.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        return $this->em->createQueryBuilder()
            ->select('a')
            ->from(Article::class, 'a')
            ->where('a.hidden = false');
    }

    /**
     * @param int $domainId
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function getVisibleByDomainIdAndUuid(int $domainId, string $uuid): Article
    {
        $article = $this->getAllVisibleQueryBuilder()
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
        $article = $this->getAllVisibleQueryBuilder()
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
