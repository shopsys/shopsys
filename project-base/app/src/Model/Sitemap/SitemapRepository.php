<?php

declare(strict_types=1);

namespace App\Model\Sitemap;

use App\Model\Blog\Article\BlogArticleRepository;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Model\Article\ArticleRepository;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Model\Sitemap\SitemapRepository as BaseSitemapRepository;
use Shopsys\FrameworkBundle\Model\Stock\ProductStock;
use Shopsys\FrameworkBundle\Model\Stock\StockDomain;

/**
 * @property \App\Model\Product\ProductRepository $productRepository
 * @property \App\Model\Category\CategoryRepository $categoryRepository
 */
class SitemapRepository extends BaseSitemapRepository
{
    /**
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \App\Model\Category\CategoryRepository $categoryRepository
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleRepository $articleRepository
     * @param \App\Model\Blog\Article\BlogArticleRepository $blogArticleRepository
     */
    public function __construct(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        ArticleRepository $articleRepository,
        private BlogArticleRepository $blogArticleRepository,
    ) {
        parent::__construct($productRepository, $categoryRepository, $articleRepository);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForVisibleCategorySeoMix(DomainConfig $domainConfig): array
    {
        $queryBuilder = $this->categoryRepository->getAllVisibleByDomainIdQueryBuilder($domainConfig->getId());
        $queryBuilder
            ->select('fu.slug')
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

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return array
     */
    public function getSitemapItemsForSoldOutProducts(DomainConfig $domainConfig, PricingGroup $pricingGroup): array
    {
        $queryBuilder = $this->productRepository->getAllVisibleQueryBuilder($domainConfig->getId(), $pricingGroup);
        $queryBuilder
            ->addSelect('fu.slug')
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

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForArticlesOnDomain(DomainConfig $domainConfig): array
    {
        $queryBuilder = $this->articleRepository->getVisibleArticlesByDomainIdQueryBuilder($domainConfig->getId());
        $queryBuilder
            ->select('fu.slug')
            ->join(
                FriendlyUrl::class,
                'fu',
                Join::WITH,
                'fu.routeName = :articlesRouteName
                AND fu.entityId = a.id
                AND fu.domainId = :domainId
                AND fu.main = TRUE',
            )
            ->setParameter('articlesRouteName', 'front_article_detail')
            ->setParameter('domainId', $domainConfig->getId());

        return $this->getSitemapItemsFromQueryBuilderWithSlugField($queryBuilder);
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
            ->select('fu.slug')
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
}
