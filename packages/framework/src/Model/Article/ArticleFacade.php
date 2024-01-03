<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleExportScheduler;

class ArticleFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleRepository $articleRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleFactoryInterface $articleFactory
     * @param \Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleExportScheduler $articleExportScheduler
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ArticleRepository $articleRepository,
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ArticleFactoryInterface $articleFactory,
        protected readonly ArticleExportScheduler $articleExportScheduler,
    ) {
    }

    /**
     * @param int $articleId
     * @return \Shopsys\FrameworkBundle\Model\Article\Article|null
     */
    public function findById(int $articleId): ?Article
    {
        return $this->articleRepository->findById($articleId);
    }

    /**
     * @param int $articleId
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function getById(int $articleId): Article
    {
        return $this->articleRepository->getById($articleId);
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getAllArticlesCountByDomainId(int $domainId): int
    {
        return $this->articleRepository->getAllArticlesCountByDomainId($domainId);
    }

    /**
     * @param int $domainId
     * @param string $placement
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrderedArticlesByDomainIdAndPlacementQueryBuilder(
        int $domainId,
        string $placement,
    ): QueryBuilder {
        return $this->articleRepository->getOrderedArticlesByDomainIdAndPlacementQueryBuilder($domainId, $placement);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleData $articleData
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function create(ArticleData $articleData): Article
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

        $this->articleExportScheduler->scheduleRowIdForImmediateExport($article->getId());

        return $article;
    }

    /**
     * @param int $articleId
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleData $articleData
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function edit(int $articleId, ArticleData $articleData): Article
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

        $this->articleExportScheduler->scheduleRowIdForImmediateExport($article->getId());

        return $article;
    }

    /**
     * @param int $articleId
     */
    public function delete(int $articleId): void
    {
        $article = $this->articleRepository->getById($articleId);

        $this->em->remove($article);
        $this->em->flush();

        $this->articleExportScheduler->scheduleRowIdForImmediateExport((int)$articleId);
    }

    /**
     * @param int[][] $rowIdsByGridId
     */
    public function saveOrdering(array $rowIdsByGridId): void
    {
        foreach ($rowIdsByGridId as $gridId => $rowIds) {
            foreach ($rowIds as $position => $rowId) {
                $article = $this->articleRepository->findById($rowId);

                if ($article === null) {
                    continue;
                }

                if ($article->getPosition() !== $position || $article->getPlacement() !== $gridId) {
                    $this->articleExportScheduler->scheduleRowIdForImmediateExport($article->getId());
                }

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
    public function getAllByDomainId(int $domainId): array
    {
        return $this->articleRepository->getAllByDomainId($domainId);
    }

    /**
     * @return string[]
     */
    public function getAvailablePlacementChoices(): array
    {
        return [
            t('Articles in footer') . ' 1' => Article::PLACEMENT_FOOTER_1,
            t('Articles in footer') . ' 2' => Article::PLACEMENT_FOOTER_2,
            t('Articles in footer') . ' 3' => Article::PLACEMENT_FOOTER_3,
            t('Articles in footer') . ' 4' => Article::PLACEMENT_FOOTER_4,
            t('without positioning') => Article::PLACEMENT_NONE,
        ];
    }
}
