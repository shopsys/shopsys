<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException;

class ArticleRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ClockInterface $clock,
    ) {
    }

    protected function getArticleRepository(): EntityRepository
    {
        return $this->em->getRepository(Article::class);
    }

    public function findById(int $articleId): ?Article
    {
        return $this->getArticleRepository()->find($articleId);
    }

    public function getOrderedArticlesByDomainIdAndPlacementQueryBuilder(
        int $domainId,
        string $placement,
    ): QueryBuilder {
        return $this->getArticlesByDomainIdQueryBuilder($domainId)
            ->andWhere('a.placement = :placement')->setParameter('placement', $placement)
            ->orderBy('a.position, a.id');
    }

    public function getArticlesByDomainIdQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('a')
            ->from(Article::class, 'a')
            ->where('a.domainId = :domainId')->setParameter('domainId', $domainId);
    }

    public function getVisibleArticlesByDomainIdQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->getAllVisibleQueryBuilder()
            ->andWhere('a.domainId = :domainId')
            ->setParameter('domainId', $domainId);
    }

    public function getAllArticlesCountByDomainId(int $domainId): int
    {
        return (int)($this->getArticlesByDomainIdQueryBuilder($domainId)
            ->select('COUNT(a)')
            ->getQuery()->getSingleScalarResult());
    }

    public function getById(int $articleId): Article
    {
        $article = $this->getArticleRepository()->find($articleId);

        if ($article === null) {
            $message = 'Article with ID ' . $articleId . ' not found';

            throw new ArticleNotFoundException($message);
        }

        return $article;
    }

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

    public function getAllVisibleQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('a')
            ->from(Article::class, 'a')
            ->where('a.hidden = false')
            ->andWhere('a.createdAt <= :now')
            ->setParameter('now', $this->clock->now());
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

    /**
     * @return int[]
     */
    public function getAllIdsByDomainId(int $domainId): array
    {
        $result = $this->getArticlesByDomainIdQueryBuilder($domainId)
            ->select('a.id')
            ->getQuery()
            ->getResult();

        return array_column($result, 'id');
    }
}
