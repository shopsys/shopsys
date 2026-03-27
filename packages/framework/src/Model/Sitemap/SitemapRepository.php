<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Sitemap;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Model\Article\ArticleRepository;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleRepository;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagRepository;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class SitemapRepository
{
    public function __construct(
        protected readonly ProductRepository $productRepository,
        protected readonly CategoryRepository $categoryRepository,
        protected readonly ArticleRepository $articleRepository,
        protected readonly BlogArticleRepository $blogArticleRepository,
        protected readonly FlagRepository $flagRepository,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForListableProducts(DomainConfig $domainConfig, PricingGroup $pricingGroup): array
    {
        $queryBuilder = $this->productRepository->getAllListableQueryBuilder($domainConfig->getId(), $pricingGroup);
        $queryBuilder
            ->select('fu.slug, fu.entityId')
            ->join(
                FriendlyUrl::class,
                'fu',
                Join::WITH,
                'fu.routeName = :productDetailRouteName
                AND fu.entityId = p.id
                AND fu.domainId = :domainId
                AND fu.main = TRUE',
            )
            ->andWhere('pd.calculatedSellingDenied = FALSE')
            ->setParameter('productDetailRouteName', 'front_product_detail')
            ->setParameter('domainId', $domainConfig->getId());

        return $this->getSitemapItemsFromQueryBuilderWithSlugField($queryBuilder);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForVisibleCategories(DomainConfig $domainConfig): array
    {
        $queryBuilder = $this->categoryRepository->getAllVisibleByDomainIdQueryBuilder($domainConfig->getId());
        $queryBuilder
            ->select('fu.slug, fu.entityId')
            ->join(
                FriendlyUrl::class,
                'fu',
                Join::WITH,
                'fu.routeName = :productListRouteName
                AND fu.entityId = c.id
                AND fu.domainId = :domainId
                AND fu.main = TRUE',
            )
            ->setParameter('productListRouteName', 'front_product_list')
            ->setParameter('domainId', $domainConfig->getId());

        return $this->getSitemapItemsFromQueryBuilderWithSlugField($queryBuilder);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForArticlesOnDomain(DomainConfig $domainConfig): array
    {
        $queryBuilder = $this->articleRepository->getVisibleArticlesByDomainIdQueryBuilder($domainConfig->getId());
        $queryBuilder
            ->select('fu.slug, fu.entityId')
            ->join(
                FriendlyUrl::class,
                'fu',
                Join::WITH,
                'fu.routeName = :articleDetailRouteName
                AND fu.entityId = a.id
                AND fu.domainId = :domainId
                AND fu.main = TRUE',
            )
            ->setParameter('articleDetailRouteName', 'front_article_detail')
            ->setParameter('domainId', $domainConfig->getId());

        return $this->getSitemapItemsFromQueryBuilderWithSlugField($queryBuilder);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    protected function getSitemapItemsFromQueryBuilderWithSlugField(QueryBuilder $queryBuilder): array
    {
        $rows = $queryBuilder->getQuery()->getScalarResult();
        $sitemapItems = [];

        foreach ($rows as $row) {
            $sitemapItem = new SitemapItem();
            $sitemapItem->slug = $row['slug'];
            $sitemapItem->id = $row['entityId'];
            $sitemapItems[] = $sitemapItem;
        }

        return $sitemapItems;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForBlogArticlesOnDomain(DomainConfig $domainConfig): array
    {
        $queryBuilder = $this->blogArticleRepository->getVisibleBlogArticlesByDomainIdAndLocaleQueryBuilder(
            $domainConfig->getId(),
            $domainConfig->getLocale(),
        );
        $queryBuilder
            ->select('fu.slug, fu.entityId')
            ->join(
                FriendlyUrl::class,
                'fu',
                Join::WITH,
                'fu.routeName = :blogArticlesRouteName
                AND fu.entityId = ba.id
                AND fu.domainId = :domainId
                AND fu.main = TRUE',
            )
            ->setParameter('blogArticlesRouteName', 'front_blogarticle_detail')
            ->setParameter('domainId', $domainConfig->getId());

        return $this->getSitemapItemsFromQueryBuilderWithSlugField($queryBuilder);
    }

    public function getSitemapItemsForSoldOutProducts(DomainConfig $domainConfig, PricingGroup $pricingGroup): array
    {
        $queryBuilder = $this->productRepository->getAllVisibleQueryBuilder($domainConfig->getId(), $pricingGroup);
        $queryBuilder
            ->addSelect('fu.slug, fu.entityId')
            ->join('p.domains', 'pd', Join::WITH, 'pd.domainId = :domainId')
            ->join(
                FriendlyUrl::class,
                'fu',
                Join::WITH,
                'fu.routeName = :productDetailRouteName
                AND fu.entityId = p.id
                AND fu.domainId = :domainId
                AND fu.main = TRUE',
            )
            ->andWhere('p.variantType != :variantTypeMain')
            ->andWhere('pd.calculatedSellingDenied = TRUE')
            ->setParameter('variantTypeMain', Product::VARIANT_TYPE_MAIN)
            ->setParameter('productDetailRouteName', 'front_product_detail')
            ->setParameter('domainId', $domainConfig->getId());

        return $this->getSitemapItemsFromQueryBuilderWithSlugField($queryBuilder);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForVisibleFlags(DomainConfig $domainConfig): array
    {
        $queryBuilder = $this->flagRepository->getVisibleQueryBuilder();
        $queryBuilder
            ->select('fu.slug, fu.entityId')
            ->join(
                FriendlyUrl::class,
                'fu',
                Join::WITH,
                'fu.routeName = :flagDetailRouteName
                AND fu.entityId = f.id
                AND fu.domainId = :domainId
                AND fu.main = TRUE',
            )
            ->setParameter('flagDetailRouteName', 'front_flag_detail')
            ->setParameter('domainId', $domainConfig->getId());

        return $this->getSitemapItemsFromQueryBuilderWithSlugField($queryBuilder);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForVisibleCategorySeoMix(DomainConfig $domainConfig): array
    {
        $queryBuilder = $this->categoryRepository->getAllVisibleByDomainIdQueryBuilder($domainConfig->getId());
        $queryBuilder
            ->select('fu.slug, fu.entityId')
            ->join(ReadyCategorySeoMix::class, 'rcsm', Join::WITH, 'rcsm.category = c AND rcsm.domainId = :domainId')
            ->join(
                FriendlyUrl::class,
                'fu',
                Join::WITH,
                'fu.routeName = :categorySeoMixRouteName
                AND fu.entityId = rcsm
                AND fu.domainId = :domainId
                AND fu.main = TRUE',
            )
            ->setParameter('categorySeoMixRouteName', 'front_category_seo')
            ->setParameter('domainId', $domainConfig->getId());

        return $this->getSitemapItemsFromQueryBuilderWithSlugField($queryBuilder);
    }
}
