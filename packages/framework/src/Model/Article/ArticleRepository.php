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
    
    public function findById(string $articleId): ?\Shopsys\FrameworkBundle\Model\Article\Article
    {
        return $this->getArticleRepository()->find($articleId);
    }
    
    public function getOrderedArticlesByDomainIdAndPlacementQueryBuilder(int $domainId, string $placement): \Doctrine\ORM\QueryBuilder
    {
        return $this->getArticlesByDomainIdQueryBuilder($domainId)
            ->andWhere('a.placement = :placement')->setParameter('placement', $placement)
            ->orderBy('a.position, a.id');
    }
    
    protected function getArticlesByDomainIdQueryBuilder(int $domainId): \Doctrine\ORM\QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('a')
            ->from(Article::class, 'a')
            ->where('a.domainId = :domainId')->setParameter('domainId', $domainId);
    }
    
    public function getVisibleArticlesByDomainIdQueryBuilder(int $domainId): \Doctrine\ORM\QueryBuilder
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
     * @return \Shopsys\FrameworkBundle\Model\Article\Article[]
     */
    public function getVisibleArticlesForPlacement(int $domainId, string $placement): array
    {
        $queryBuilder = $this->getVisibleArticlesByDomainIdQueryBuilder($domainId)
            ->andWhere('a.placement = :placement')->setParameter('placement', $placement)
            ->orderBy('a.position, a.id');

        return $queryBuilder->getQuery()->execute();
    }
    
    public function getById(int $articleId): \Shopsys\FrameworkBundle\Model\Article\Article
    {
        $article = $this->getArticleRepository()->find($articleId);
        if ($article === null) {
            $message = 'Article with ID ' . $articleId . ' not found';
            throw new \Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException($message);
        }
        return $article;
    }
    
    public function getVisibleById(int $articleId): \Shopsys\FrameworkBundle\Model\Article\Article
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
     * @return \Shopsys\FrameworkBundle\Model\Article\Article[]
     */
    public function getAllByDomainId(int $domainId): array
    {
        return $this->getArticleRepository()->findBy([
            'domainId' => $domainId,
        ]);
    }
}
