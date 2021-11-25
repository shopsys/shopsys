<?php

declare(strict_types=1);

namespace App\Model\Blog\Article\Elasticsearch;

use App\Component\Breadcrumb\BreadcrumbFacade;
use App\Model\Blog\Article\BlogArticle;
use App\Model\Blog\Article\BlogArticleFacade;
use App\Model\Blog\Article\BlogArticleRepository;
use App\Model\Blog\Category\BlogCategory;
use App\Model\Product\ProductFacade;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class BlogArticleExportRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var \App\Model\Blog\Article\BlogArticleRepository
     */
    private BlogArticleRepository $blogArticleRepository;

    /**
     * @var \App\Model\Blog\Article\BlogArticleFacade
     */
    private BlogArticleFacade $blogArticleFacade;

    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @var \App\Component\Breadcrumb\BreadcrumbFacade
     */
    private BreadcrumbFacade $breadcrumbFacade;

    /**
     * @var \App\Model\Product\ProductFacade
     */
    private ProductFacade $productFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Blog\Article\BlogArticleRepository $blogArticleRepository
     * @param \App\Model\Blog\Article\BlogArticleFacade $blogArticleFacade
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Component\Breadcrumb\BreadcrumbFacade $breadcrumbFacade
     * @param \App\Model\Product\ProductFacade $productFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        BlogArticleRepository $blogArticleRepository,
        BlogArticleFacade $blogArticleFacade,
        FriendlyUrlFacade $friendlyUrlFacade,
        BreadcrumbFacade $breadcrumbFacade,
        ProductFacade $productFacade
    ) {
        $this->blogArticleRepository = $blogArticleRepository;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->em = $em;
        $this->breadcrumbFacade = $breadcrumbFacade;
        $this->productFacade = $productFacade;
        $this->blogArticleFacade = $blogArticleFacade;
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param int $limit
     * @param int $lastProcessedId
     * @return \App\Model\Blog\Article\BlogArticle[]
     */
    public function getVisibleBlogArticlesByDomainIdAndLocale(
        int $domainId,
        string $locale,
        int $limit,
        int $lastProcessedId
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
     * @param int $domainId
     * @param string $locale
     * @param int[] $blogArticleIds
     * @return \App\Model\Blog\Article\BlogArticle[]
     */
    public function getVisibleBlogArticlesByDomainIdAndLocaleAndBlogArticleIds(
        int $domainId,
        string $locale,
        array $blogArticleIds
    ): array {
        return $this->blogArticleRepository->getVisibleBlogArticlesByDomainIdAndLocaleQueryBuilder($domainId, $locale)
            ->andWhere('ba.id IN (:blogArticleIds)')
            ->setParameter('blogArticleIds', $blogArticleIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @return int
     */
    public function getVisibleBlogArticlesCountByDomainIdAndLocale(int $domainId, string $locale): int
    {
        return (int)($this->em->createQueryBuilder()
            ->select('COUNT(ba)')
            ->from(BlogArticle::class, 'ba')
            ->join('ba.translations', 'bat', Join::WITH, 'bat.locale = :locale')
            ->setParameter('locale', $locale)
            ->join('ba.domains', 'bad', Join::WITH, 'bad.domainId = :domainId')
            ->andWhere('ba.publishDate <= :todayDate')
            ->andWhere('bad.visible = true')
            ->andWhere('ba.hidden = false')
            ->setParameter('todayDate', (new DateTime())->format('Y-m-d H:i:s'))
            ->setParameter('domainId', $domainId)
            ->getQuery()->getSingleScalarResult());
    }

    /**
     * @param \App\Model\Blog\Article\BlogArticle $blogArticle
     * @param int $domainId
     * @param string $locale
     * @return array
     */
    public function extractBlogArticle(BlogArticle $blogArticle, int $domainId, string $locale): array
    {
        $blogArticleCategories = $blogArticle->getBlogCategoriesIndexedByDomainId()[$domainId];
        $mainFriendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl($domainId, 'front_blogarticle_detail', $blogArticle->getId());

        return [
            'name' => $blogArticle->getName($locale),
            'text' => $blogArticle->getDescription($locale),
            'url' => $this->friendlyUrlFacade->getAbsoluteUrlByFriendlyUrl($mainFriendlyUrl),
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
            'products' => $this->extractProductsFromDescription($blogArticle->getDescription($locale)),
            'breadcrumb' => $this->breadcrumbFacade->getBreadcrumbOnDomain($blogArticle->getId(), 'front_blogarticle_detail', $domainId, $locale),
        ];
    }

    /**
     * @param string|null $description
     * @return int[]
     */
    private function extractProductsFromDescription(?string $description): array
    {
        $productCatnums = $this->blogArticleFacade->getProductCatnumsFromDescription($description);

        $products = $this->productFacade->findAllByCatnums($productCatnums);

        $productIds = [];
        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }

        return $productIds;
    }
}
