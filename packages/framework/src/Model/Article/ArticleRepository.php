<?php

namespace Shopsys\FrameworkBundle\Model\Article;

use Doctrine\ORM\EntityManagerInterface;

class ArticleRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function getArticleRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(Article::class);
    }

    /**
     * @param string $articleId
     */
    public function findById($articleId): ?\Shopsys\FrameworkBundle\Model\Article\Article
    {
        return $this->getArticleRepository()->find($articleId);
    }

    /**
     * @param int $domainId
     * @param string $placement
     */
    public function getOrderedArticlesByDomainIdAndPlacementQueryBuilder($domainId, $placement): \Doctrine\ORM\QueryBuilder
    {
        return $this->getArticlesByDomainIdQueryBuilder($domainId)
            ->andWhere('a.placement = :placement')->setParameter('placement', $placement)
            ->orderBy('a.position, a.id');
    }

    /**
     * @param int $domainId
     */
    protected function getArticlesByDomainIdQueryBuilder($domainId): \Doctrine\ORM\QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('a')
            ->from(Article::class, 'a')
            ->where('a.domainId = :domainId')->setParameter('domainId', $domainId);
    }

    /**
     * @param int $domainId
     */
    public function getVisibleArticlesByDomainIdQueryBuilder($domainId): \Doctrine\ORM\QueryBuilder
    {
        return $this->getAllVisibleQueryBuilder()
            ->andWhere('a.domainId = :domainId')
            ->setParameter('domainId', $domainId);
    }

    public function getAllArticlesCountByDomainId($domainId): int
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
    public function getVisibleArticlesForPlacement($domainId, $placement): array
    {
        $queryBuilder = $this->getVisibleArticlesByDomainIdQueryBuilder($domainId)
            ->andWhere('a.placement = :placement')->setParameter('placement', $placement)
            ->orderBy('a.position, a.id');

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param int $articleId
     */
    public function getById($articleId): \Shopsys\FrameworkBundle\Model\Article\Article
    {
        $article = $this->getArticleRepository()->find($articleId);
        if ($article === null) {
            $message = 'Article with ID ' . $articleId . ' not found';
            throw new \Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException($message);
        }
        return $article;
    }

    /**
     * @param int $articleId
     */
    public function getVisibleById($articleId): \Shopsys\FrameworkBundle\Model\Article\Article
    {
        $article = $this->getAllVisibleQueryBuilder()
            ->andWhere('a.id = :articleId')
            ->setParameter('articleId', $articleId)
            ->getQuery()->getOneOrNullResult();

        if ($article === null) {
            $message = 'Article with ID ' . $articleId . ' not found';
            throw new \Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException($message);
        }
        return $article;
    }

    protected function getAllVisibleQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('a')
            ->from(Article::class, 'a')
            ->where('a.hidden = false');
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Article\Article[]
     */
    public function getAllByDomainId($domainId): array
    {
        return $this->getArticleRepository()->findBy([
            'domainId' => $domainId,
        ]);
    }
}
