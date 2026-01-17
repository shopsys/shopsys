<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Psr\Clock\ClockInterface;
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
        protected readonly EntityManagerInterface $em,
        protected readonly BlogArticleRepository $blogArticleRepository,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly BreadcrumbFacade $breadcrumbFacade,
        protected readonly GrapesJsParser $grapesJsParser,
        protected readonly ImageFacade $imageFacade,
        protected readonly Domain $domain,
        protected readonly HreflangLinksFacade $hreflangLinksFacade,
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly ClockInterface $clock,
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
        return $this->blogArticleRepository->getVisibleBlogArticlesByDomainIdAndLocaleQueryBuilder($domainId, $locale)
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
        return $this->blogArticleRepository->getVisibleBlogArticlesByDomainIdAndLocaleQueryBuilder($domainId, $locale)
            ->andWhere('ba.id IN (:blogArticleIds)')
            ->setParameter('blogArticleIds', $blogArticleIds)
            ->getQuery()
            ->getResult();
    }

    public function getVisibleBlogArticlesCountByDomainIdAndLocale(int $domainId, string $locale): int
    {
        return (int)($this->em->createQueryBuilder()
            ->select('COUNT(ba)')
            ->from(BlogArticle::class, 'ba')
            ->join('ba.translations', 'bat', Join::WITH, 'bat.locale = :locale')
            ->setParameter('locale', $locale)
            ->join('ba.domains', 'bad', Join::WITH, 'bad.domainId = :domainId')
            ->andWhere('ba.publishDate <= :now')
            ->andWhere('bad.visible = true')
            ->andWhere('ba.hidden = false')
            ->setParameter('now', $this->clock->now())
            ->setParameter('domainId', $domainId)
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

        return [
            'name' => $blogArticle->getName($locale),
            'text' => $this->grapesJsParser->parse($blogArticle->getDescription($locale)),
            'uuid' => $blogArticle->getUuid(),
            'createdAt' => $blogArticle->getCreatedAt()->format('Y-m-d H:i:s'),
            'visibleOnHomepage' => $blogArticle->isVisibleOnHomepage(),
            'publishDate' => $blogArticle->getPublishDate()->format('Y-m-d H:i:s'),
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
        ];
    }
}
