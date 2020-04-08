<?php

declare(strict_types=1);

namespace App\Model\Sitemap;

use App\Model\Stock\ProductStock;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Sitemap\SitemapRepository as BaseSitemapRepository;

/**
 * @property \App\Model\Product\ProductRepository $productRepository
 * @property \App\Model\Category\CategoryRepository $categoryRepository
 * @method __construct(\App\Model\Product\ProductRepository $productRepository, \App\Model\Category\CategoryRepository $categoryRepository, \Shopsys\FrameworkBundle\Model\Article\ArticleRepository $articleRepository)
 */
class SitemapRepository extends BaseSitemapRepository
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForVisibleProducts(DomainConfig $domainConfig, PricingGroup $pricingGroup)
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
                AND fu.main = TRUE'
            )
            ->setParameter('productDetailRouteName', 'front_product_detail')
            ->setParameter('domainId', $domainConfig->getId());

        $subquery = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from(ProductStock::class, 'ps')
            ->join('ps.stock', 's', Join::WITH, 's.domainId = :domainId')
            ->where('ps.product = p')
            ->having('SUM(ps.productQuantity) > 0');

        $this->productRepository->addDomain($queryBuilder, $domainConfig->getId());
        $queryBuilder->andWhere('pd.saleExclusion = false OR EXISTS(' . $subquery->getDQL() . ')');

        return $this->getSitemapItemsFromQueryBuilderWithSlugField($queryBuilder);
    }
}
