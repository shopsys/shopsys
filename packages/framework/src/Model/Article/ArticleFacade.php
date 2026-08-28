<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Article\Messenger\ArticleExportMessageDispatcher;

class ArticleFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ArticleRepository $articleRepository,
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ArticleFactory $articleFactory,
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
        protected readonly ArticleExportMessageDispatcher $articleExportMessageDispatcher,
    ) {
    }

    public function findById(int $articleId): ?Article
    {
        return $this->articleRepository->findById($articleId);
    }

    public function getById(int $articleId): Article
    {
        return $this->articleRepository->getById($articleId);
    }

    public function getAllArticlesCountByDomainId(int $domainId): int
    {
        return $this->articleRepository->getAllArticlesCountByDomainId($domainId);
    }

    public function getOrderedArticlesByDomainIdAndPlacementQueryBuilder(
        int $domainId,
        string $placement,
    ): QueryBuilder {
        return $this->articleRepository->getOrderedArticlesByDomainIdAndPlacementQueryBuilder($domainId, $placement);
    }

    public function create(ArticleData $articleData): Article
    {
        $article = $this->articleFactory->create($articleData);

        $this->em->persist($article);
        $this->em->flush();

        if (!$article->isLinkType()) {
            $this->friendlyUrlFacade->createFriendlyUrlForDomain(
                'front_article_detail',
                $article->getId(),
                $article->getName(),
                $article->getDomainId(),
            );
            $this->em->flush();
        }

        $this->articleExportMessageDispatcher->dispatchArticleExportMessage($article->getId(), $article->getDomainId());
        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::ARTICLES_QUERY_KEY_PART);

        return $article;
    }

    public function edit(int $articleId, ArticleData $articleData): Article
    {
        $article = $this->articleRepository->getById($articleId);

        $article->edit($articleData);

        if (!$article->isLinkType()) {
            $this->friendlyUrlFacade->saveUrlListFormData('front_article_detail', $article->getId(), $articleData->urls);
            $this->friendlyUrlFacade->createFriendlyUrlForDomain(
                'front_article_detail',
                $article->getId(),
                $article->getName(),
                $article->getDomainId(),
            );
        }
        $this->em->flush();

        $this->articleExportMessageDispatcher->dispatchArticleExportMessage($article->getId(), $article->getDomainId());
        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::ARTICLES_QUERY_KEY_PART);

        return $article;
    }

    public function delete(int $articleId): void
    {
        $article = $this->articleRepository->getById($articleId);

        $this->em->remove($article);
        $this->em->flush();

        $this->articleExportMessageDispatcher->dispatchArticleExportMessage($articleId, $article->getDomainId());
        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::ARTICLES_QUERY_KEY_PART);
    }

    /**
     * @param array<string, array<int|string, int>> $rowIdsByGridId
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
                    $this->articleExportMessageDispatcher->dispatchArticleExportMessage($article->getId(), $article->getDomainId());
                }

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

    /**
     * @return int[]
     */
    public function getAllIdsByDomainId(int $domainId): array
    {
        return $this->articleRepository->getAllIdsByDomainId($domainId);
    }
}
