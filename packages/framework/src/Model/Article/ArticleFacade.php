<?php

namespace Shopsys\FrameworkBundle\Model\Article;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class ArticleFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Article\ArticleRepository
     */
    protected $articleRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    protected $friendlyUrlFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Article\ArticleFactoryInterface
     */
    protected $articleFactory;

    public function __construct(
        EntityManagerInterface $em,
        ArticleRepository $articleRepository,
        Domain $domain,
        FriendlyUrlFacade $friendlyUrlFacade,
        ArticleFactoryInterface $articleFactory
    ) {
        $this->em = $em;
        $this->articleRepository = $articleRepository;
        $this->domain = $domain;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->articleFactory = $articleFactory;
    }
    
    public function findById(int $articleId): ?\Shopsys\FrameworkBundle\Model\Article\Article
    {
        return $this->articleRepository->findById($articleId);
    }
    
    public function getById(int $articleId): \Shopsys\FrameworkBundle\Model\Article\Article
    {
        return $this->articleRepository->getById($articleId);
    }
    
    public function getVisibleById(int $articleId): \Shopsys\FrameworkBundle\Model\Article\Article
    {
        return $this->articleRepository->getVisibleById($articleId);
    }

    public function getAllArticlesCountByDomainId($domainId): int
    {
        return $this->articleRepository->getAllArticlesCountByDomainId($domainId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Article\Article[]
     */
    public function getVisibleArticlesForPlacementOnCurrentDomain(string $placement): array
    {
        return $this->articleRepository->getVisibleArticlesForPlacement($this->domain->getId(), $placement);
    }
    
    public function getOrderedArticlesByDomainIdAndPlacementQueryBuilder(int $domainId, string $placement): \Doctrine\ORM\QueryBuilder
    {
        return $this->articleRepository->getOrderedArticlesByDomainIdAndPlacementQueryBuilder($domainId, $placement);
    }

    public function create(ArticleData $articleData): \Shopsys\FrameworkBundle\Model\Article\Article
    {
        $article = $this->articleFactory->create($articleData);

        $this->em->persist($article);
        $this->em->flush();
        $this->friendlyUrlFacade->createFriendlyUrlForDomain(
            'front_article_detail',
            $article->getId(),
            $article->getName(),
            $article->getDomainId()
        );
        $this->em->flush();

        return $article;
    }
    
    public function edit(int $articleId, ArticleData $articleData): \Shopsys\FrameworkBundle\Model\Article\Article
    {
        $article = $this->articleRepository->getById($articleId);
        $article->edit($articleData);

        $this->friendlyUrlFacade->saveUrlListFormData('front_article_detail', $article->getId(), $articleData->urls);
        $this->friendlyUrlFacade->createFriendlyUrlForDomain(
            'front_article_detail',
            $article->getId(),
            $article->getName(),
            $article->getDomainId()
        );
        $this->em->flush();

        return $article;
    }
    
    public function delete(int $articleId): void
    {
        $article = $this->articleRepository->getById($articleId);

        $this->em->remove($article);
        $this->em->flush();
    }

    /**
     * @param string[][] $rowIdsByGridId
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
     * @return \Shopsys\FrameworkBundle\Model\Article\Article[]
     */
    public function getAllByDomainId(int $domainId): array
    {
        return $this->articleRepository->getAllByDomainId($domainId);
    }
}
