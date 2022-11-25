<?php

namespace Shopsys\FrameworkBundle\Model\Article;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException;

class ArticleRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getArticleRepository(): EntityRepository
    {
        return $this->em->getRepository(Article::class);
    }

    /**
     * @param int $articleId
     * @return \Shopsys\FrameworkBundle\Model\Article\Article|null
     */
    public function findById(int $articleId): ?Article
    {
        return $this->getArticleRepository()->find($articleId);
    }

    /**
     * @param int $domainId
     * @param string $placement
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrderedArticlesByDomainIdAndPlacementQueryBuilder(int $domainId, string $placement): QueryBuilder
    {
        return $this->getArticlesByDomainIdQueryBuilder($domainId)
            ->andWhere('a.placement = :placement')->setParameter('placement', $placement)
            ->orderBy('a.position, a.id');
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticlesByDomainIdQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('a')
            ->from(Article::class, 'a')
            ->where('a.domainId = :domainId')->setParameter('domainId', $domainId);
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getVisibleArticlesByDomainIdQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->getAllVisibleQueryBuilder()
            ->andWhere('a.domainId = :domainId')
            ->setParameter('domainId', $domainId);
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getAllArticlesCountByDomainId(int $domainId): int
    {
        return (int)($this->getArticlesByDomainIdQueryBuilder($domainId)
            ->select('COUNT(a)')
            ->getQuery()->getSingleScalarResult());
    }

    /**
     * @param int $domainId
     * @param string $placement
     * @return \Shopsys\FrameworkBundle\Model\Article\Article[]
     */
    public function getVisibleArticlesForPlacement(int $domainId, string $placement): array
    {
        $queryBuilder = $this->getVisibleArticlesByDomainIdAndPlacementSortedByPositionQueryBuilder(
            $domainId,
            $placement
        );

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param int $articleId
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function getById(int $articleId): Article
    {
        $article = $this->getArticleRepository()->find($articleId);
        if ($article === null) {
            $message = 'Article with ID ' . $articleId . ' not found';
            throw new ArticleNotFoundException($message);
        }
        return $article;
    }

    /**
     * @param int $articleId
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function getVisibleById(int $articleId): Article
    {
        $article = $this->getAllVisibleQueryBuilder()
            ->andWhere('a.id = :articleId')
            ->setParameter('articleId', $articleId)
            ->getQuery()->getOneOrNullResult();

        if ($article === null) {
            $message = 'Article with ID ' . $articleId . ' not found';
            throw new ArticleNotFoundException($message);
        }
        return $article;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllVisibleQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('a')
            ->from(Article::class, 'a')
            ->where('a.hidden = false');
    }

    /**
     * @param int $domainId
     * @return object[]
     */
    public function getAllByDomainId(int $domainId): array
    {
        return $this->getArticleRepository()->findBy([
            'domainId' => $domainId,
        ]);
    }

    /**
     * @param int $domainId
     * @param string $placement
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getVisibleArticlesByDomainIdAndPlacementSortedByPositionQueryBuilder(
        int $domainId,
        string $placement
    ): QueryBuilder {
        return $this->getVisibleArticlesByDomainIdQueryBuilder($domainId)
            ->andWhere('a.placement = :placement')->setParameter('placement', $placement)
            ->orderBy('a.position, a.id');
    }
}
