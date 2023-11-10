<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\Model\Category\Category as AppCategory;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Exception\InvalidOrderingModeException;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductCalculatedPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductDomain;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository as BaseProductRepository;
use Shopsys\FrameworkBundle\Model\Stock\ProductStock;

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
 * @method \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult getPaginationResultForListableInCategory(\App\Model\Category\Category $category, int $domainId, string $locale, \App\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, int $page, int $limit)
 * @method \Doctrine\ORM\QueryBuilder getAllListableTranslatedAndOrderedQueryBuilderByCategory(int $domainId, string $locale, string $orderingModeId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, \App\Model\Category\Category $category)
 * @method \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult getPaginationResultForListableForBrand(\App\Model\Product\Brand\Brand $brand, int $domainId, string $locale, string $orderingModeId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, int $page, int $limit)
 * @method \App\Model\Product\Product getById(int $id)
 * @method \App\Model\Product\Product[] getAllByIds(int[] $ids)
 * @method \App\Model\Product\Product getVisible(int $id, int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @method \App\Model\Product\Product getSellableById(int $id, int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @method \Doctrine\ORM\Internal\Hydration\IterableResult|\App\Model\Product\Product[][] getProductIteratorForReplaceVat()
 * @method \Doctrine\ORM\Internal\Hydration\IterableResult|\App\Model\Product\Product[][] getProductsForPriceRecalculationIterator()
 * @method \Doctrine\ORM\Internal\Hydration\IterableResult|\App\Model\Product\Product[][] getProductsForAvailabilityRecalculationIterator()
 * @method \App\Model\Product\Product[] getAtLeastSomewhereSellableVariantsByMainVariant(\App\Model\Product\Product $mainVariant)
 * @method \App\Model\Product\Product[] getOfferedByIds(int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, int[] $sortedProductIds)
 * @method \App\Model\Product\Product[] getListableByIds(int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, int[] $sortedProductIds)
 * @method \App\Model\Product\Product getOneByCatnumExcludeMainVariants(string $productCatnum)
 * @method \App\Model\Product\Product getOneByUuid(string $uuid)
 * @method markProductsForExport(\App\Model\Product\Product[] $products)
 * @method \App\Model\Product\Product[] getProductsWithBrand(\App\Model\Product\Brand\Brand $brand)
 * @method \App\Model\Product\Product[] getProductsWithFlag(\App\Model\Product\Flag\Flag $flag)
 * @method \App\Model\Product\Product[] getProductsWithUnit(\App\Model\Product\Unit\Unit $unit)
 * @property \App\Component\Doctrine\QueryBuilderExtender $queryBuilderExtender
 * @method array getProductsWithParameter(\App\Model\Product\Parameter\Parameter $parameter)
 * @property \App\Model\Product\Filter\ProductFilterRepository $productFilterRepository
 * @method \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult getPaginationResultForSearchListable(string|null $searchText, int $domainId, string $locale, \App\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, int $page, int $limit)
 * @method \Doctrine\ORM\QueryBuilder getFilteredListableForSearchQueryBuilder(string|null $searchText, int $domainId, string $locale, \App\Model\Product\Filter\ProductFilterData $productFilterData, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @method \App\Model\Product\Product[] getAllSellableVariantsByMainVariant(\App\Model\Product\Product $mainVariant, int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 */
class ProductRepository extends BaseProductRepository
{
    /**
     * @param array $productCatnums
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \App\Model\Product\Product[]
     */
    public function getVisibleProductsByCatnumsAndDomainId(
        array $productCatnums,
        int $domainId,
        PricingGroup $pricingGroup,
    ): array {
        return $this->getAllVisibleQueryBuilder($domainId, $pricingGroup)
            ->andWhere('p.catnum IN (:catnums)')
            ->andWhere('p.sellingDenied = FALSE')
            ->setParameter('catnums', $productCatnums)
            ->getQuery()
            ->execute();
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
            ->join('ps.stock', 's')
            ->join('s.domains', 'sd', Join::WITH, 's.id = sd.stock AND sd.domainId = :domainId AND sd.isEnabled = TRUE')
            ->where('ps.product = p')
            ->setParameter('domainId', $domainId)
            ->having('SUM(ps.productQuantity) > 0');

        $queryBuilder->andWhere('(EXISTS(' . $subquery->getDQL() . ') OR p.preorder = true) AND pd.saleExclusion = false AND p.calculatedSellingDenied = false');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\Availability $availability
     * @return \App\Model\Product\Product[]
     */
    public function getProductsWithAvailability(Availability $availability): array
    {
        throw new Exception('Product Availability is deprecated');
    }

    /**
     * @param string $catnum
     * @return \App\Model\Product\Product|null
     */
    public function findByCatnum(string $catnum): ?Product
    {
        $queryBuilder = $this->getProductRepository()
            ->createQueryBuilder('p')
            ->andWhere('p.catnum = :catnum')
            ->setParameter('catnum', $catnum);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * @param array $catnums
     * @return \App\Model\Product\Product[]
     */
    public function findAllByCatnums(array $catnums): array
    {
        $queryBuilder = $this->getProductRepository()
            ->createQueryBuilder('p', 'p.catnum')
            ->andWhere('p.catnum IN (:catnum)')
            ->setParameter('catnum', $catnums);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult|\App\Model\Product\Product[][]
     */
    public function getProductIteratorForReplaceVat()
    {
        $query = $this->em->createQuery('
            SELECT distinct p
            FROM ' . Product::class . ' p
            JOIN ' . ProductDomain::class . ' pd WITH pd.product = p
            JOIN pd.vat v
            WHERE v.replaceWith IS NOT NULL
        ');

        return $query->iterate();
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param string $locale
     * @param string|null $searchText
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListableBySearchTextQueryBuilder(
        $domainId,
        PricingGroup $pricingGroup,
        $locale,
        $searchText,
    ) {
        $queryBuilder = $this->getAllListableQueryBuilder($domainId, $pricingGroup);

        $this->addTranslation($queryBuilder, $locale);

        $this->productElasticsearchRepository->filterBySearchText($queryBuilder, $searchText);

        return $queryBuilder;
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param string $locale
     * @param string|null $searchText
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSellableBySearchTextQueryBuilder(
        $domainId,
        PricingGroup $pricingGroup,
        $locale,
        $searchText,
    ) {
        $queryBuilder = $this->getAllSellableQueryBuilder($domainId, $pricingGroup);

        $this->addTranslation($queryBuilder, $locale);

        $this->productElasticsearchRepository->filterBySearchText($queryBuilder, $searchText);

        return $queryBuilder;
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param int $domainId
     * @param string $locale
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFilteredListableInCategoryQueryBuilder(
        Category $category,
        $domainId,
        $locale,
        ProductFilterData $productFilterData,
        PricingGroup $pricingGroup,
    ) {
        $queryBuilder = $this->getListableInCategoryQueryBuilder(
            $domainId,
            $pricingGroup,
            $category,
        );

        $this->addTranslation($queryBuilder, $locale);
        $this->productFilterRepository->applyFiltering(
            $queryBuilder,
            $productFilterData,
            $pricingGroup,
        );

        return $queryBuilder;
    }

    /**
     * @param \App\Model\Category\Category $category
     * @return \App\Model\Product\Product[]
     */
    public function getProductsByCategory(AppCategory $category): array
    {
        return $this->getAllProductsQueryBuilder()
            ->join('p.productCategoryDomains', 'pcd')
            ->where('pcd.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->execute();
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllSellableQueryBuilder($domainId, PricingGroup $pricingGroup): QueryBuilder
    {
        return $this->getAllOfferedQueryBuilder($domainId, $pricingGroup)
            ->join(ProductDomain::class, 'pd', Join::WITH, 'pd.product = p AND pd.domainId = :domainId')
            ->andWhere('p.variantType != :variantTypeMain')
            ->andWhere('pd.saleExclusion = false')
            ->setParameter('variantTypeMain', Product::VARIANT_TYPE_MAIN)
            ->setParameter('domainId', $domainId);
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string $orderingModeId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param string $locale
     */
    protected function applyOrdering(
        QueryBuilder $queryBuilder,
        $orderingModeId,
        PricingGroup $pricingGroup,
        $locale,
    ) {
        if ($orderingModeId === ProductListOrderingConfig::ORDER_BY_RELEVANCE) {
            $queryBuilder->addOrderBy('relevance', 'asc');
            $queryBuilder->addOrderBy('p.id', 'asc');

            return;
        }
        $queryBuilder->resetDQLPart('orderBy');

        //TODO product has no calculatedAvailability attribute (CC), is directed by stocks, BUT this is no god and stock manipulation has to be transformed to this calculatedAvailability
//        $queryBuilder->join('p.calculatedAvailability', 'pca');
//        $queryBuilder->addSelect('CASE WHEN pca.dispatchTime IS NULL THEN 1 ELSE 0 END as HIDDEN dispatchTimeIsNull');
//        $queryBuilder->orderBy('dispatchTimeIsNull', 'ASC');
//        $queryBuilder->addOrderBy('pca.dispatchTime', 'ASC');

        switch ($orderingModeId) {
            case ProductListOrderingConfig::ORDER_BY_NAME_ASC:
                $collation = $this->localization->getCollationByLocale($locale);
                $queryBuilder->addOrderBy("COLLATE(pt.name, '" . $collation . "')", 'asc');

                break;

            case ProductListOrderingConfig::ORDER_BY_NAME_DESC:
                $collation = $this->localization->getCollationByLocale($locale);
                $queryBuilder->addOrderBy("COLLATE(pt.name, '" . $collation . "')", 'desc');

                break;

            case ProductListOrderingConfig::ORDER_BY_PRICE_ASC:
                $this->queryBuilderExtender->addOrExtendJoin(
                    $queryBuilder,
                    ProductCalculatedPrice::class,
                    'pcp',
                    'pcp.product = p AND pcp.pricingGroup = :pricingGroup',
                );
                $queryBuilder->addOrderBy('pcp.priceWithVat', 'asc');
                $queryBuilder->setParameter('pricingGroup', $pricingGroup);

                break;

            case ProductListOrderingConfig::ORDER_BY_PRICE_DESC:
                $this->queryBuilderExtender->addOrExtendJoin(
                    $queryBuilder,
                    ProductCalculatedPrice::class,
                    'pcp',
                    'pcp.product = p AND pcp.pricingGroup = :pricingGroup',
                );
                $queryBuilder->addOrderBy('pcp.priceWithVat', 'desc');
                $queryBuilder->setParameter('pricingGroup', $pricingGroup);

                break;

            case ProductListOrderingConfig::ORDER_BY_PRIORITY:
                $queryBuilder->addOrderBy('p.orderingPriority', 'desc');
                $collation = $this->localization->getCollationByLocale($locale);
                $queryBuilder->addOrderBy("COLLATE(pt.name, '" . $collation . "')", 'asc');

                break;

            default:
                $message = 'Product list ordering mode "' . $orderingModeId . '" is not supported.';

                throw new InvalidOrderingModeException($message);
        }

        $queryBuilder->addOrderBy('p.id', 'asc');
    }

    /**
     * @param string[] $catnums
     * @return int[]
     */
    public function getProductIdsByCatnums(array $catnums): array
    {
        $result = $this->em->createQueryBuilder()
            ->select('p.id')
            ->from(Product::class, 'p')
            ->where('p.catnum IN (:catnums)')
            ->setParameter('catnums', $catnums)
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'id');
    }
}
