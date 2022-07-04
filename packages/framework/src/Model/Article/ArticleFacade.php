<?php

namespace Shopsys\FrameworkBundle\Model\Article;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Symfony\Component\Config\Definition\Exception\Exception;

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

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleRepository $articleRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleFactoryInterface $articleFactory
     */
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

    /**
     * @param int $articleId
     * @return \Shopsys\FrameworkBundle\Model\Article\Article|null
     */
    public function findById($articleId)
    {
        return $this->articleRepository->findById($articleId);
    }

    /**
     * @param int $articleId
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function getById($articleId)
    {
        return $this->articleRepository->getById($articleId);
    }

    /**
     * @param int $articleId
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function getVisibleById($articleId)
    {
        return $this->articleRepository->getVisibleById($articleId);
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getAllArticlesCountByDomainId($domainId)
    {
        return $this->articleRepository->getAllArticlesCountByDomainId($domainId);
    }

    /**
     * @param string $placement
     * @return \Shopsys\FrameworkBundle\Model\Article\Article[]
     */
    public function getVisibleArticlesForPlacementOnCurrentDomain($placement)
    {
        return $this->articleRepository->getVisibleArticlesForPlacement($this->domain->getId(), $placement);
    }

    /**
     * @param int $domainId
     * @param string $placement
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrderedArticlesByDomainIdAndPlacementQueryBuilder($domainId, $placement)
    {
        return $this->articleRepository->getOrderedArticlesByDomainIdAndPlacementQueryBuilder($domainId, $placement);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleData $articleData
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function create(ArticleData $articleData)
    {
        $this->dismantleArticleData($articleData);

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

    /**
     * @param int $articleId
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleData $articleData
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function edit($articleId, ArticleData $articleData)
    {
        $article = $this->articleRepository->getById($articleId);
        $originalName = $article->getName();

        $this->dismantleArticleData($articleData);

        $article->edit($articleData);
        $this->friendlyUrlFacade->saveUrlListFormData('front_article_detail', $article->getId(), $articleData->urls);

        if ($originalName !== $article->getName()) {
            $this->friendlyUrlFacade->createFriendlyUrlForDomain(
                'front_article_detail',
                $article->getId(),
                $article->getName(),
                $article->getDomainId()
            );
        }
        $this->em->flush();

        return $article;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleData $articleData
     */
    protected function dismantleArticleData(ArticleData $articleData): void
    {
        // error 500 thrown when trying to save changes after a certain url was marked for deletion
        if (isset($articleData->urls->toDelete[1]) && count($articleData->urls->toDelete[1]) > 0) {
            throw new Exception();
        }

        // disable text alignment in the wysiwyg editor
        $articleData->text = preg_replace(
            '/"text-align: (left|right|center|justify);"/',
            'text-align: "";',
            $articleData->text
        );
    }

    /**
     * @param int $articleId
     */
    public function delete($articleId)
    {
        $article = $this->articleRepository->getById($articleId);

        $this->em->remove($article);
        $this->em->flush();
    }

    /**
     * @param int[][] $rowIdsByGridId
     */
    public function saveOrdering(array $rowIdsByGridId)
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
    public function getAllByDomainId($domainId)
    {
        return $this->articleRepository->getAllByDomainId($domainId);
    }

    /**
     * @return string[]
     */
    public function getAvailablePlacementChoices(): array
    {
        return [
            t('in upper menu') => Article::PLACEMENT_TOP_MENU,
            t('in footer') => Article::PLACEMENT_FOOTER,
            t('without positioning') => Article::PLACEMENT_NONE,
        ];
    }
}
