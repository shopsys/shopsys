<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\Model\Stock\ProductStock;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository as BaseProductRepository;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibility;

/**
 * @property \App\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Product\Filter\ProductFilterRepository $productFilterRepository, \App\Component\Doctrine\QueryBuilderExtender $queryBuilderExtender, \Shopsys\FrameworkBundle\Model\Localization\Localization $localization, \App\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository)
 * @method \App\Model\Product\Product|null findById(int $id)
 * @method \Doctrine\ORM\QueryBuilder getListableInCategoryQueryBuilder(int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, \App\Model\Category\Category $category)
 * @method \Doctrine\ORM\QueryBuilder getListableForBrandQueryBuilder(int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, \App\Model\Product\Brand\Brand $brand)
 * @method \Doctrine\ORM\QueryBuilder getSellableInCategoryQueryBuilder(int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, \App\Model\Category\Category $category)
 * @method \Doctrine\ORM\QueryBuilder getOfferedInCategoryQueryBuilder(int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, \App\Model\Category\Category $category)
 * @method filterByCategory(\Doctrine\ORM\QueryBuilder $queryBuilder, \App\Model\Category\Category $category, int $domainId)
 * @method filterByBrand(\Doctrine\ORM\QueryBuilder $queryBuilder, \App\Model\Product\Brand\Brand $brand)
 * @method \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult getPaginationResultForListableInCategory(\App\Model\Category\Category $category, int $domainId, string $locale, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, int $page, int $limit)
 * @method \Doctrine\ORM\QueryBuilder getAllListableTranslatedAndOrderedQueryBuilderByCategory(int $domainId, string $locale, string $orderingModeId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, \App\Model\Category\Category $category)
 * @method \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult getPaginationResultForListableForBrand(\App\Model\Product\Brand\Brand $brand, int $domainId, string $locale, string $orderingModeId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, int $page, int $limit)
 * @method \Doctrine\ORM\QueryBuilder getFilteredListableInCategoryQueryBuilder(\App\Model\Category\Category $category, int $domainId, string $locale, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @method \App\Model\Product\Product getById(int $id)
 * @method \App\Model\Product\Product[] getAllByIds(int[] $ids)
 * @method \App\Model\Product\Product getVisible(int $id, int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @method \App\Model\Product\Product getSellableById(int $id, int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @method \Doctrine\ORM\Internal\Hydration\IterableResult|\App\Model\Product\Product[][] getProductIteratorForReplaceVat()
 * @method \Doctrine\ORM\Internal\Hydration\IterableResult|\App\Model\Product\Product[][] getProductsForPriceRecalculationIterator()
 * @method \Doctrine\ORM\Internal\Hydration\IterableResult|\App\Model\Product\Product[][] getProductsForAvailabilityRecalculationIterator()
 * @method \App\Model\Product\Product[] getAllSellableVariantsByMainVariant(\App\Model\Product\Product $mainVariant, int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @method \App\Model\Product\Product[] getAtLeastSomewhereSellableVariantsByMainVariant(\App\Model\Product\Product $mainVariant)
 * @method \App\Model\Product\Product[] getOfferedByIds(int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, int[] $sortedProductIds)
 * @method \App\Model\Product\Product[] getListableByIds(int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, int[] $sortedProductIds)
 * @method \App\Model\Product\Product getOneByCatnumExcludeMainVariants(string $productCatnum)
 * @method \App\Model\Product\Product getOneByUuid(string $uuid)
 * @method markProductsForExport(\App\Model\Product\Product[] $products)
 * @method \App\Model\Product\Product[] getProductsWithBrand(\App\Model\Product\Brand\Brand $brand)
 * @method \App\Model\Product\Product[] getProductsWithFlag(\App\Model\Product\Flag\Flag $flag)
 * @method \App\Model\Product\Product[] getProductsWithUnit(\Shopsys\FrameworkBundle\Model\Product\Unit\Unit $unit)
 * @property \App\Component\Doctrine\QueryBuilderExtender $queryBuilderExtender
 * @method array getProductsWithParameter(\App\Model\Product\Parameter\Parameter $parameter)
 * @property \App\Model\Product\Filter\ProductFilterRepository $productFilterRepository
 */
class ProductRepository extends BaseProductRepository
{
    /**
     * @param array $productCatnums
     * @param int $domainId
     * @return \App\Model\Product\Product[]
     */
    public function getVisibleProductsByCatnumsAndDomainId(array $productCatnums, int $domainId): array
    {
        return $this->getAllVisibleQueryBuilder($domainId, null)
            ->andWhere('p.catnum IN (:catnums)')
            ->andWhere('p.sellingDenied = FALSE')
            ->setParameter('catnums', $productCatnums)
            ->getQuery()
            ->execute();
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup|null $pricingGroup
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllVisibleQueryBuilder($domainId, ?PricingGroup $pricingGroup = null): QueryBuilder
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p')
            ->join(ProductVisibility::class, 'prv', Join::WITH, 'prv.product = p.id')
            ->where('prv.domainId = :domainId')
            ->andWhere('prv.visible = TRUE')
            ->orderBy('p.id');

        $queryBuilder->setParameter('domainId', $domainId);

        return $queryBuilder;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllProductsQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p');
    }

    /**
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function getProductsWithoutProductTypePlanFilesIterator(): IterableResult
    {
        return $this->getAllProductsQueryBuilder()
            ->where('p.downloadProductTypePlanFiles = true')
            ->getQuery()
            ->iterate();
    }

    /**
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function getProductsWithoutAssemblyInstructionFilesIterator(): IterableResult
    {
        return $this->getAllProductsQueryBuilder()
            ->where('p.downloadAssemblyInstructionFiles = true')
            ->getQuery()
            ->iterate();
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param int $domainId
     */
    public function filterTemporaryExcludedProducts(QueryBuilder $queryBuilder, int $domainId): void
    {
        $subquery = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from(ProductStock::class, 'ps')
            ->join('ps.stock', 's', Join::WITH, 's.domainId = :domainId')
            ->where('ps.product = p')
            ->setParameter('domainId', $domainId)
            ->having('SUM(ps.productQuantity) > 0');

        $queryBuilder->andWhere('EXISTS(' . $subquery->getDQL() . ') OR (p.preorder = true AND pd.saleExclusion = false)');
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     */
    public function filterSellingDenied(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->andWhere('p.sellingDenied = FALSE');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\Availability $availability
     * @return \App\Model\Product\Product[]
     */
    public function getProductsWithAvailability(Availability $availability): array
    {
        throw new \Exception('Product Availability is deprecated');
    }
}
