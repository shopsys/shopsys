<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class ArticleFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleRepository $articleRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleFactoryInterface $articleFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ArticleRepository $articleRepository,
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ArticleFactoryInterface $articleFactory,
    ) {
    }

    /**
     * @param int $articleId
     * @return \Shopsys\FrameworkBundle\Model\Article\Article|null
     */
    public function findById($articleId): ?\Shopsys\FrameworkBundle\Model\Article\Article
    {
        return $this->articleRepository->findById($articleId);
    }

    /**
     * @param int $articleId
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function getById($articleId): \Shopsys\FrameworkBundle\Model\Article\Article
    {
        return $this->articleRepository->getById($articleId);
    }

    /**
     * @param int $articleId
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function getVisibleById($articleId): \Shopsys\FrameworkBundle\Model\Article\Article
    {
        return $this->articleRepository->getVisibleById($articleId);
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getAllArticlesCountByDomainId($domainId): int
    {
        return $this->articleRepository->getAllArticlesCountByDomainId($domainId);
    }

    /**
     * @param string $placement
     * @return \Shopsys\FrameworkBundle\Model\Article\Article[]
     */
    public function getVisibleArticlesForPlacementOnCurrentDomain($placement): array
    {
        return $this->articleRepository->getVisibleArticlesForPlacement($this->domain->getId(), $placement);
    }

    /**
     * @param int $domainId
     * @param string $placement
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrderedArticlesByDomainIdAndPlacementQueryBuilder($domainId, $placement): \Doctrine\ORM\QueryBuilder
    {
        return $this->articleRepository->getOrderedArticlesByDomainIdAndPlacementQueryBuilder($domainId, $placement);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleData $articleData
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function create(ArticleData $articleData): \Shopsys\FrameworkBundle\Model\Article\Article
    {
        $article = $this->articleFactory->create($articleData);

        $this->em->persist($article);
        $this->em->flush();
        $this->friendlyUrlFacade->createFriendlyUrlForDomain(
            'front_article_detail',
            $article->getId(),
            $article->getName(),
            $article->getDomainId(),
        );
        $this->em->flush();

        return $article;
    }

    /**
     * @param int $articleId
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleData $articleData
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function edit($articleId, ArticleData $articleData): \Shopsys\FrameworkBundle\Model\Article\Article
    {
        $article = $this->articleRepository->getById($articleId);
        $originalName = $article->getName();

        $article->edit($articleData);
        $this->friendlyUrlFacade->saveUrlListFormData('front_article_detail', $article->getId(), $articleData->urls);

        if ($originalName !== $article->getName()) {
            $this->friendlyUrlFacade->createFriendlyUrlForDomain(
                'front_article_detail',
                $article->getId(),
                $article->getName(),
                $article->getDomainId(),
            );
        }
        $this->em->flush();

        return $article;
    }

    /**
     * @param int $articleId
     */
    public function delete($articleId): void
    {
        $article = $this->articleRepository->getById($articleId);

        $this->em->remove($article);
        $this->em->flush();
    }

    /**
     * @param int[][] $rowIdsByGridId
     */
    public function saveOrdering(array $rowIdsByGridId): void
    {
        foreach ($rowIdsByGridId as $gridId => $rowIds) {
            foreach ($rowIds as $position => $rowId) {
                $article = $this->articleRepository->findById($rowId);
                $article->setPosition($position);
                $article->setPlacement($gridId);
            }
        }
        $this->em->flush();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Article\Article[]
     */
    public function getAllByDomainId($domainId): array
    {
        return $this->articleRepository->getAllByDomainId($domainId);
    }

    /**
     * @return string[]
     */
    public function getAvailablePlacementChoices(): array
    {
        return [
            t('in footer') => Article::PLACEMENT_FOOTER,
            t('without positioning') => Article::PLACEMENT_NONE,
        ];
    }
}
