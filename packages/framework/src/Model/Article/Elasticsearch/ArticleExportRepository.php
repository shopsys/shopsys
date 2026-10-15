<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbFacade;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentFactory;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentTextExtractor;
use Shopsys\FrameworkBundle\Component\ContentEditor\Export\ContentDocumentExporter;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleRepository;

class ArticleExportRepository
{
    public function __construct(
        protected readonly ArticleRepository $articleRepository,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly BreadcrumbFacade $breadcrumbFacade,
        protected readonly ContentDocumentFactory $contentDocumentFactory,
        protected readonly ContentDocumentTextExtractor $contentDocumentTextExtractor,
        protected readonly ContentDocumentExporter $contentDocumentExporter,
    ) {
    }

    public function getVisibleArticlesCountByDomainId(int $domainId): int
    {
        return (int)($this->articleRepository->getVisibleArticlesByDomainIdQueryBuilder($domainId)
            ->select('COUNT(a)')
            ->getQuery()->getSingleScalarResult());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Article\Article[]
     */
    public function getAllVisibleArticlesByDomainId(int $domainId, int $limit, int $lastProcessedId): array
    {
        return $this->articleRepository->getVisibleArticlesByDomainIdQueryBuilder($domainId)
            ->andWhere('a.id > :lastProcessedId')
            ->setParameter('lastProcessedId', $lastProcessedId)
            ->setMaxResults($limit)
            ->orderBy('a.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int[] $articleIds
     * @return \Shopsys\FrameworkBundle\Model\Article\Article[]
     */
    public function getVisibleArticlesByDomainIdAndArticleIds(int $domainId, array $articleIds): array
    {
        return $this->articleRepository->getVisibleArticlesByDomainIdQueryBuilder($domainId)
            ->andWhere('a.id IN (:articleIds)')
            ->setParameter('articleIds', $articleIds)
            ->getQuery()
            ->getResult();
    }

    public function extractArticle(Article $article): array
    {
        $domainId = $article->getDomainId();
        $articleId = $article->getId();
        $document = $this->contentDocumentFactory->createFromArray($article->getContentArray() ?? []);

        $extractedArticle = [
            'name' => $article->getName(),
            'text' => $this->contentDocumentTextExtractor->extractText($document),
            'content' => $this->contentDocumentExporter->export($document, $domainId),
            'url' => $article->getUrl(),
            'uuid' => $article->getUuid(),
            'placement' => $article->getPlacement(),
            'seoH1' => $article->getSeoAttributes()->getH1(),
            'seoTitle' => $article->getSeoAttributes()->getTitle(),
            'seoMetaDescription' => $article->getSeoAttributes()->getMetaDescription(),
            'seoMetaRobots' => $article->getSeoAttributes()->getMetaRobots(),
            'seoCanonicalUrl' => $article->getSeoAttributes()->getCanonicalUrl(),
            'position' => $article->getPosition(),
            'external' => $article->isExternal(),
            'createdAt' => $article->getCreatedAt()->format('Y-m-d H:i:s'),
            'modifiedAt' => $article->getModifiedAt()?->format('Y-m-d H:i:s'),
            'publishDate' => $article->getPublishDate()?->format('Y-m-d H:i:s'),
            'type' => $article->getType(),
            'slug' => [],
            'mainSlug' => null,
            'breadcrumb' => [],
        ];

        if ($article->isSiteType()) {
            $mainFriendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl($domainId, 'front_article_detail', $articleId);

            $extractedArticle['slug'] = $this->friendlyUrlFacade->getAllSlugsByRouteNameAndEntityId($domainId, 'front_article_detail', $articleId);
            $extractedArticle['mainSlug'] = $mainFriendlyUrl->getSlug();
            $extractedArticle['breadcrumb'] = $this->breadcrumbFacade->getBreadcrumbOnDomain($articleId, 'front_article_detail', $domainId);
        }

        return $extractedArticle;
    }
}
