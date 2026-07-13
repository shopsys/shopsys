<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\GrapesJs\GrapesJsParser;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleRepository;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;

class BlogArticleExportRepository
{
    public function __construct(
        protected readonly BlogArticleRepository $blogArticleRepository,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly BreadcrumbFacade $breadcrumbFacade,
        protected readonly GrapesJsParser $grapesJsParser,
        protected readonly ImageFacade $imageFacade,
        protected readonly Domain $domain,
        protected readonly HreflangLinksFacade $hreflangLinksFacade,
        protected readonly BlogCategoryFacade $blogCategoryFacade,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle[]
     */
    public function getVisibleBlogArticlesByDomainIdAndLocale(
        int $domainId,
        string $locale,
        int $limit,
        int $lastProcessedId,
    ): array {
        return $this->blogArticleRepository->getExportableBlogArticlesByDomainIdAndLocaleQueryBuilder($domainId, $locale)
            ->andWhere('ba.id > :lastProcessedId')
            ->setParameter('lastProcessedId', $lastProcessedId)
            ->setMaxResults($limit)
            ->orderBy('ba.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int[] $blogArticleIds
     * @return \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle[]
     */
    public function getVisibleBlogArticlesByDomainIdAndLocaleAndBlogArticleIds(
        int $domainId,
        string $locale,
        array $blogArticleIds,
    ): array {
        return $this->blogArticleRepository->getExportableBlogArticlesByDomainIdAndLocaleQueryBuilder($domainId, $locale)
            ->andWhere('ba.id IN (:blogArticleIds)')
            ->setParameter('blogArticleIds', $blogArticleIds)
            ->getQuery()
            ->getResult();
    }

    public function getVisibleBlogArticlesCountByDomainIdAndLocale(int $domainId, string $locale): int
    {
        return (int)($this->blogArticleRepository->getExportableBlogArticlesByDomainIdAndLocaleQueryBuilder($domainId, $locale)
            ->select('COUNT(ba)')
            ->resetDQLPart('orderBy')
            ->getQuery()->getSingleScalarResult());
    }

    public function extractBlogArticle(BlogArticle $blogArticle, int $domainId, string $locale): array
    {
        $blogArticleCategories = $blogArticle->getBlogCategoriesIndexedByDomainId()[$domainId];
        $mainFriendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl($domainId, 'front_blogarticle_detail', $blogArticle->getId());
        $domainConfig = $this->domain->getDomainConfigById($domainId);

        try {
            $imageUrl = $this->imageFacade->getImageUrl($domainConfig, $blogArticle);
        } catch (ImageNotFoundException $exception) {
            $imageUrl = null;
        }

        $blogArticleAuthor = $blogArticle->getBlogArticleAuthor();

        return [
            'name' => $blogArticle->getName($locale),
            'text' => $this->grapesJsParser->parse($blogArticle->getDescription($locale)),
            'uuid' => $blogArticle->getUuid(),
            'createdAt' => $blogArticle->getCreatedAt()->format('Y-m-d H:i:s'),
            'visibleOnHomepage' => $blogArticle->isVisibleOnHomepage(),
            'publishDate' => $blogArticle->getPublishDate($domainId)?->format('Y-m-d H:i:s'),
            'status' => $blogArticle->getStatus($domainId),
            'perex' => $blogArticle->getPerex($locale),
            'seoTitle' => $blogArticle->getSeoTitle($domainId),
            'seoMetaDescription' => $blogArticle->getSeoMetaDescription($domainId),
            'seoH1' => $blogArticle->getSeoH1($domainId),
            'slug' => $this->friendlyUrlFacade->getAllSlugsByRouteNameAndEntityId($domainId, 'front_blogarticle_detail', $blogArticle->getId()),
            'categories' => array_map(fn (BlogCategory $blogCategory) => $blogCategory->getId(), $blogArticleCategories),
            'mainSlug' => $mainFriendlyUrl->getSlug(),
            'breadcrumb' => $this->breadcrumbFacade->getBreadcrumbOnDomain($blogArticle->getId(), 'front_blogarticle_detail', $domainId, $locale),
            'imageUrl' => $imageUrl,
            'hreflangLinks' => $this->hreflangLinksFacade->getForBlogArticle($blogArticle, $domainId, false),
            'mainBlogCategoryUuid' => $this->blogCategoryFacade->getBlogArticleMainBlogCategoryOnDomain(
                $blogArticle,
                $domainId,
            )->getUuid(),
            'author' => $blogArticleAuthor === null ? null : [
                'id' => $blogArticleAuthor->getId(),
                'uuid' => $blogArticleAuthor->getUuid(),
                'name' => $blogArticleAuthor->getName(),
                'jobTitle' => $blogArticleAuthor->getJobTitle($locale),
                'description' => $blogArticleAuthor->getDescription($locale),
            ],
        ];
    }
}
