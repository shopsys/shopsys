<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Sitemap;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Model\Article\ArticleRepository;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleRepository;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Model\Stock\ProductStock;
use Shopsys\FrameworkBundle\Model\Stock\StockDomain;

class SitemapRepository
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryRepository $categoryRepository
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleRepository $articleRepository
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleRepository $blogArticleRepository
     */
    public function __construct(
        protected readonly ProductRepository $productRepository,
        protected readonly CategoryRepository $categoryRepository,
        protected readonly ArticleRepository $articleRepository,
        protected readonly BlogArticleRepository $blogArticleRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
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
            ->setParameter('productDetailRouteName', 'front_product_detail')
            ->setParameter('domainId', $domainConfig->getId());

        return $this->getSitemapItemsFromQueryBuilderWithSlugField($queryBuilder);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
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
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
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
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    protected function getSitemapItemsFromQueryBuilderWithSlugField(QueryBuilder $queryBuilder): array
    {
        $rows = $queryBuilder->getQuery()->execute(null, AbstractQuery::HYDRATE_SCALAR);
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
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
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

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return array
     */
    public function getSitemapItemsForSoldOutProducts(DomainConfig $domainConfig, PricingGroup $pricingGroup): array
    {
        $queryBuilder = $this->productRepository->getAllVisibleQueryBuilder($domainConfig->getId(), $pricingGroup);
        $queryBuilder
            ->addSelect('fu.slug, fu.entityId')
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
            ->setParameter('variantTypeMain', Product::VARIANT_TYPE_MAIN)
            ->setParameter('productDetailRouteName', 'front_product_detail')
            ->setParameter('domainId', $domainConfig->getId());

        $subquery = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from(ProductStock::class, 'ps')
            ->join(StockDomain::class, 'sd', Join::WITH, 'ps.stock = sd.stock AND sd.domainId = :domainId')
            ->where('ps.product = p')
            ->having('SUM(ps.productQuantity) = 0');

        $this->productRepository->addDomain($queryBuilder, $domainConfig->getId());
        $queryBuilder->andWhere('EXISTS(' . $subquery->getDQL() . ') AND (pd.saleExclusion = true)');

        return $this->getSitemapItemsFromQueryBuilderWithSlugField($queryBuilder);
    }
}
