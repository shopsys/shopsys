<?php

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderService;
use Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterRepository;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeService;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductCalculatedPrice;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductSearchRepository;

class ProductRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterRepository
     */
    protected $productFilterRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderService
     */
    protected $queryBuilderService;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    protected $localization;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\ProductSearchRepository
     */
    protected $productSearchRepository;

    public function __construct(
        EntityManagerInterface $em,
        ProductFilterRepository $productFilterRepository,
        QueryBuilderService $queryBuilderService,
        Localization $localization,
        ProductSearchRepository $productSearchRepository
    ) {
        $this->em = $em;
        $this->productFilterRepository = $productFilterRepository;
        $this->queryBuilderService = $queryBuilderService;
        $this->localization = $localization;
        $this->productSearchRepository = $productSearchRepository;
    }

    protected function getProductRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(Product::class);
    }
    
    public function findById(int $id): ?\Shopsys\FrameworkBundle\Model\Product\Product
    {
        return $this->getProductRepository()->find($id);
    }
    
    public function getAllListableQueryBuilder(int $domainId, PricingGroup $pricingGroup): \Doctrine\ORM\QueryBuilder
    {
        $queryBuilder = $this->getAllOfferedQueryBuilder($domainId, $pricingGroup);
        $queryBuilder->andWhere('p.variantType != :variantTypeVariant')
            ->setParameter('variantTypeVariant', Product::VARIANT_TYPE_VARIANT);

        return $queryBuilder;
    }
    
    public function getAllSellableQueryBuilder(int $domainId, PricingGroup $pricingGroup): \Doctrine\ORM\QueryBuilder
    {
        $queryBuilder = $this->getAllOfferedQueryBuilder($domainId, $pricingGroup);
        $queryBuilder->andWhere('p.variantType != :variantTypeMain')
            ->setParameter('variantTypeMain', Product::VARIANT_TYPE_MAIN);

        return $queryBuilder;
    }
    
    public function getAllOfferedQueryBuilder(int $domainId, PricingGroup $pricingGroup): \Doctrine\ORM\QueryBuilder
    {
        $queryBuilder = $this->getAllVisibleQueryBuilder($domainId, $pricingGroup);
        $queryBuilder->andWhere('p.calculatedSellingDenied = FALSE');

        return $queryBuilder;
    }
    
    public function getAllVisibleQueryBuilder(int $domainId, PricingGroup $pricingGroup): \Doctrine\ORM\QueryBuilder
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p')
            ->join(ProductVisibility::class, 'prv', Join::WITH, 'prv.product = p.id')
            ->where('prv.domainId = :domainId')
                ->andWhere('prv.pricingGroup = :pricingGroup')
                ->andWhere('prv.visible = TRUE')
            ->orderBy('p.id');

        $queryBuilder->setParameter('domainId', $domainId);
        $queryBuilder->setParameter('pricingGroup', $pricingGroup);

        return $queryBuilder;
    }
    
    public function addTranslation(QueryBuilder $queryBuilder, string $locale): void
    {
        $queryBuilder->addSelect('pt')
            ->join('p.translations', 'pt', Join::WITH, 'pt.locale = :locale');

        $queryBuilder->setParameter('locale', $locale);
    }
    
    public function addDomain(QueryBuilder $queryBuilder, int $domainId): void
    {
        $queryBuilder->addSelect('pd')
            ->join('p.domains', 'pd', Join::WITH, 'pd.domainId = :domainId');

        $queryBuilder->setParameter('domainId', $domainId);
    }
    
    public function getListableInCategoryQueryBuilder(
        int $domainId,
        PricingGroup $pricingGroup,
        Category $category
    ): \Doctrine\ORM\QueryBuilder {
        $queryBuilder = $this->getAllListableQueryBuilder($domainId, $pricingGroup);
        $this->filterByCategory($queryBuilder, $category, $domainId);
        return $queryBuilder;
    }
    
    protected function getListableForBrandQueryBuilder(
        int $domainId,
        PricingGroup $pricingGroup,
        Brand $brand
    ): \Doctrine\ORM\QueryBuilder {
        $queryBuilder = $this->getAllListableQueryBuilder($domainId, $pricingGroup);
        $this->filterByBrand($queryBuilder, $brand);
        return $queryBuilder;
    }
    
    public function getSellableInCategoryQueryBuilder(
        int $domainId,
        PricingGroup $pricingGroup,
        Category $category
    ): \Doctrine\ORM\QueryBuilder {
        $queryBuilder = $this->getAllSellableQueryBuilder($domainId, $pricingGroup);
        $this->filterByCategory($queryBuilder, $category, $domainId);
        return $queryBuilder;
    }
    
    public function getOfferedInCategoryQueryBuilder(
        int $domainId,
        PricingGroup $pricingGroup,
        Category $category
    ): \Doctrine\ORM\QueryBuilder {
        $queryBuilder = $this->getAllOfferedQueryBuilder($domainId, $pricingGroup);
        $this->filterByCategory($queryBuilder, $category, $domainId);

        return $queryBuilder;
    }

    public function getListableBySearchTextQueryBuilder(
        int $domainId,
        PricingGroup $pricingGroup,
        string $locale,
        ?string $searchText
    ): \Doctrine\ORM\QueryBuilder {
        $queryBuilder = $this->getAllListableQueryBuilder($domainId, $pricingGroup);

        $this->addTranslation($queryBuilder, $locale);
        $this->addDomain($queryBuilder, $domainId);

        $this->productSearchRepository->filterBySearchText($queryBuilder, $searchText);

        return $queryBuilder;
    }
    
    protected function filterByCategory(QueryBuilder $queryBuilder, Category $category, int $domainId): void
    {
        $queryBuilder->join('p.productCategoryDomains', 'pcd', Join::WITH, 'pcd.category = :category AND pcd.domainId = :domainId');
        $queryBuilder->setParameter('category', $category);
        $queryBuilder->setParameter('domainId', $domainId);
    }

    protected function filterByBrand(QueryBuilder $queryBuilder, Brand $brand): void
    {
        $queryBuilder->andWhere('p.brand = :brand');
        $queryBuilder->setParameter('brand', $brand);
    }
    
    public function getPaginationResultForListableInCategory(
        Category $category,
        int $domainId,
        string $locale,
        ProductFilterData $productFilterData,
        string $orderingModeId,
        PricingGroup $pricingGroup,
        int $page,
        int $limit
    ): \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult {
        $queryBuilder = $this->getFilteredListableInCategoryQueryBuilder(
            $category,
            $domainId,
            $locale,
            $productFilterData,
            $pricingGroup
        );

        $this->applyOrdering($queryBuilder, $orderingModeId, $pricingGroup, $locale);

        $queryPaginator = new QueryPaginator($queryBuilder);

        return $queryPaginator->getResult($page, $limit);
    }
    
    public function getPaginationResultForListableForBrand(
        Brand $brand,
        int $domainId,
        string $locale,
        string $orderingModeId,
        PricingGroup $pricingGroup,
        int $page,
        int $limit
    ): \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult {
        $queryBuilder = $this->getListableForBrandQueryBuilder(
            $domainId,
            $pricingGroup,
            $brand
        );

        $this->addTranslation($queryBuilder, $locale);
        $this->addDomain($queryBuilder, $domainId);
        $this->applyOrdering($queryBuilder, $orderingModeId, $pricingGroup, $locale);

        $queryPaginator = new QueryPaginator($queryBuilder);

        return $queryPaginator->getResult($page, $limit);
    }
    
    public function getFilteredListableInCategoryQueryBuilder(
        Category $category,
        int $domainId,
        string $locale,
        ProductFilterData $productFilterData,
        PricingGroup $pricingGroup
    ): \Doctrine\ORM\QueryBuilder {
        $queryBuilder = $this->getListableInCategoryQueryBuilder(
            $domainId,
            $pricingGroup,
            $category
        );

        $this->addTranslation($queryBuilder, $locale);
        $this->addDomain($queryBuilder, $domainId);
        $this->productFilterRepository->applyFiltering(
            $queryBuilder,
            $productFilterData,
            $pricingGroup
        );

        return $queryBuilder;
    }

    public function getPaginationResultForSearchListable(
        ?string $searchText,
        int $domainId,
        string $locale,
        ProductFilterData $productFilterData,
        string $orderingModeId,
        PricingGroup $pricingGroup,
        int $page,
        int $limit
    ): \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult {
        $queryBuilder = $this->getFilteredListableForSearchQueryBuilder(
            $searchText,
            $domainId,
            $locale,
            $productFilterData,
            $pricingGroup
        );

        $this->productSearchRepository->addRelevance($queryBuilder, $searchText);
        $this->applyOrdering($queryBuilder, $orderingModeId, $pricingGroup, $locale);

        $queryPaginator = new QueryPaginator($queryBuilder);

        return $queryPaginator->getResult($page, $limit);
    }

    public function getFilteredListableForSearchQueryBuilder(
        ?string $searchText,
        int $domainId,
        string $locale,
        ProductFilterData $productFilterData,
        PricingGroup $pricingGroup
    ): \Doctrine\ORM\QueryBuilder {
        $queryBuilder = $this->getListableBySearchTextQueryBuilder(
            $domainId,
            $pricingGroup,
            $locale,
            $searchText
        );

        $this->productFilterRepository->applyFiltering(
            $queryBuilder,
            $productFilterData,
            $pricingGroup
        );

        return $queryBuilder;
    }
    
    protected function applyOrdering(
        QueryBuilder $queryBuilder,
        string $orderingModeId,
        PricingGroup $pricingGroup,
        string $locale
    ): void {
        switch ($orderingModeId) {
            case ProductListOrderingModeService::ORDER_BY_NAME_ASC:
                $collation = $this->localization->getCollationByLocale($locale);
                $queryBuilder->orderBy("COLLATE(pt.name, '" . $collation . "')", 'asc');
                break;

            case ProductListOrderingModeService::ORDER_BY_NAME_DESC:
                $collation = $this->localization->getCollationByLocale($locale);
                $queryBuilder->orderBy("COLLATE(pt.name, '" . $collation . "')", 'desc');
                break;

            case ProductListOrderingModeService::ORDER_BY_PRICE_ASC:
                $this->queryBuilderService->addOrExtendJoin(
                    $queryBuilder,
                    ProductCalculatedPrice::class,
                    'pcp',
                    'pcp.product = p AND pcp.pricingGroup = :pricingGroup'
                );
                $queryBuilder->orderBy('pcp.priceWithVat', 'asc');
                $queryBuilder->setParameter('pricingGroup', $pricingGroup);
                break;

            case ProductListOrderingModeService::ORDER_BY_PRICE_DESC:
                $this->queryBuilderService->addOrExtendJoin(
                    $queryBuilder,
                    ProductCalculatedPrice::class,
                    'pcp',
                    'pcp.product = p AND pcp.pricingGroup = :pricingGroup'
                );
                $queryBuilder->orderBy('pcp.priceWithVat', 'desc');
                $queryBuilder->setParameter('pricingGroup', $pricingGroup);
                break;

            case ProductListOrderingModeService::ORDER_BY_RELEVANCE:
                $queryBuilder->orderBy('relevance', 'asc');
                break;

            case ProductListOrderingModeService::ORDER_BY_PRIORITY:
                $queryBuilder->orderBy('p.orderingPriority', 'desc');
                $collation = $this->localization->getCollationByLocale($locale);
                $queryBuilder->addOrderBy("COLLATE(pt.name, '" . $collation . "')", 'asc');
                break;

            default:
                $message = 'Product list ordering mode "' . $orderingModeId . '" is not supported.';
                throw new \Shopsys\FrameworkBundle\Model\Product\Exception\InvalidOrderingModeException($message);
        }

        $queryBuilder->addOrderBy('p.id', 'asc');
    }
    
    public function getById(int $id): \Shopsys\FrameworkBundle\Model\Product\Product
    {
        $product = $this->findById($id);

        if ($product === null) {
            throw new \Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException('Product with ID ' . $id . ' does not exist.');
        }

        return $product;
    }

    /**
     * @param int[] $ids
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getAllByIds($ids): array
    {
        return $this->getProductRepository()->findBy(['id' => $ids]);
    }
    
    public function getVisible(int $id, int $domainId, PricingGroup $pricingGroup): \Shopsys\FrameworkBundle\Model\Product\Product
    {
        $qb = $this->getAllVisibleQueryBuilder($domainId, $pricingGroup);
        $qb->andWhere('p.id = :productId');
        $qb->setParameter('productId', $id);

        $product = $qb->getQuery()->getOneOrNullResult();

        if ($product === null) {
            throw new \Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException();
        }

        return $product;
    }
    
    public function getSellableById(int $id, int $domainId, PricingGroup $pricingGroup): \Shopsys\FrameworkBundle\Model\Product\Product
    {
        $qb = $this->getAllSellableQueryBuilder($domainId, $pricingGroup);
        $qb->andWhere('p.id = :productId');
        $qb->setParameter('productId', $id);

        $product = $qb->getQuery()->getOneOrNullResult();

        if ($product === null) {
            throw new \Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException();
        }

        return $product;
    }

    /**
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult|\Shopsys\FrameworkBundle\Model\Product\Product[][]
     */
    public function getProductIteratorForReplaceVat()
    {
        $query = $this->em->createQuery('
            SELECT p
            FROM ' . Product::class . ' p
            JOIN p.vat v
            WHERE v.replaceWith IS NOT NULL
        ');

        return $query->iterate();
    }

    public function markAllProductsForAvailabilityRecalculation(): void
    {
        $this->em
            ->createQuery('UPDATE ' . Product::class . ' p SET p.recalculateAvailability = TRUE
                WHERE p.recalculateAvailability = FALSE')
            ->execute();
    }

    public function markAllProductsForPriceRecalculation(): void
    {
        // Performance optimization:
        // Main variant price recalculation is triggered by variants visibility recalculation
        // and visibility recalculation is triggered by variant price recalculation.
        // Therefore main variant price recalculation is useless here.
        $this->em
            ->createQuery('UPDATE ' . Product::class . ' p SET p.recalculatePrice = TRUE
                WHERE p.variantType != :variantTypeMain AND p.recalculateAvailability = FALSE')
            ->setParameter('variantTypeMain', Product::VARIANT_TYPE_MAIN)
            ->execute();
    }

    /**
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult|\Shopsys\FrameworkBundle\Model\Product\Product[][]
     */
    public function getProductsForPriceRecalculationIterator()
    {
        return $this->getProductRepository()
            ->createQueryBuilder('p')
            ->where('p.recalculatePrice = TRUE')
            ->getQuery()
            ->iterate();
    }

    /**
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult|\Shopsys\FrameworkBundle\Model\Product\Product[][]
     */
    public function getProductsForAvailabilityRecalculationIterator()
    {
        return $this->getProductRepository()
            ->createQueryBuilder('p')
            ->where('p.recalculateAvailability = TRUE')
            ->getQuery()
            ->iterate();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getAllSellableVariantsByMainVariant(Product $mainVariant, int $domainId, PricingGroup $pricingGroup): array
    {
        $queryBuilder = $this->getAllSellableQueryBuilder($domainId, $pricingGroup);
        $queryBuilder
            ->andWhere('p.mainVariant = :mainVariant')
            ->setParameter('mainVariant', $mainVariant);

        return $queryBuilder->getQuery()->execute();
    }
    
    public function getAllSellableUsingStockInStockQueryBuilder(int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup): \Doctrine\ORM\QueryBuilder
    {
        $queryBuilder = $this->getAllSellableQueryBuilder($domainId, $pricingGroup);
        $queryBuilder
            ->andWhere('p.usingStock = TRUE')
            ->andWhere('p.stockQuantity > 0');

        return $queryBuilder;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getAtLeastSomewhereSellableVariantsByMainVariant(Product $mainVariant): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p')
            ->andWhere('p.calculatedVisibility = TRUE')
            ->andWhere('p.calculatedSellingDenied = FALSE')
            ->andWhere('p.variantType = :variantTypeVariant')->setParameter('variantTypeVariant', Product::VARIANT_TYPE_VARIANT)
            ->andWhere('p.mainVariant = :mainVariant')->setParameter('mainVariant', $mainVariant);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param int[] $productIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getOfferedByIds(int $domainId, PricingGroup $pricingGroup, array $productIds): array
    {
        if (count($productIds) === 0) {
            return [];
        }

        $queryBuilder = $this->getAllOfferedQueryBuilder($domainId, $pricingGroup);
        $queryBuilder->andWhere('p.id IN (:productIds)')->setParameter('productIds', $productIds);

        return $queryBuilder->getQuery()->execute();
    }
    
    public function getOneByCatnumExcludeMainVariants(string $productCatnum): \Shopsys\FrameworkBundle\Model\Product\Product
    {
        $queryBuilder = $this->getProductRepository()->createQueryBuilder('p')
            ->andWhere('p.catnum = :catnum')
            ->andWhere('p.variantType != :variantTypeMain')
            ->setParameter('catnum', $productCatnum)
            ->setParameter('variantTypeMain', Product::VARIANT_TYPE_MAIN);
        $product = $queryBuilder->getQuery()->getOneOrNullResult();

        if ($product === null) {
            throw new \Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException(
                'Product with catnum ' . $productCatnum . ' does not exist.'
            );
        }

        return $product;
    }
}
