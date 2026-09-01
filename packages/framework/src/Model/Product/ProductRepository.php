<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderExtender;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;

class ProductRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ProductElasticsearchRepository $productElasticsearchRepository,
        protected readonly QueryBuilderExtender $queryBuilderExtender,
    ) {
    }

    protected function getProductRepository(): EntityRepository
    {
        return $this->em->getRepository(Product::class);
    }

    public function findById(int $id): ?Product
    {
        return $this->getProductRepository()->find($id);
    }

    public function getAllListableQueryBuilder(int $domainId, PricingGroup $pricingGroup): QueryBuilder
    {
        $queryBuilder = $this->getAllOfferedQueryBuilder($domainId, $pricingGroup);
        $queryBuilder->andWhere('p.variantType != :variantTypeVariant')
            ->setParameter('variantTypeVariant', Product::VARIANT_TYPE_VARIANT);

        return $queryBuilder;
    }

    public function getAllSellableQueryBuilder(int $domainId, PricingGroup $pricingGroup): QueryBuilder
    {
        $queryBuilder = $this->getAllOfferedQueryBuilder($domainId, $pricingGroup);
        $queryBuilder->andWhere('p.variantType != :variantTypeMain')
            ->setParameter('variantTypeMain', Product::VARIANT_TYPE_MAIN);

        return $queryBuilder;
    }

    public function getAllSellableWithoutInquiriesQueryBuilder(int $domainId, PricingGroup $pricingGroup): QueryBuilder
    {
        $queryBuilder = $this->getAllSellableQueryBuilder($domainId, $pricingGroup);
        $queryBuilder->andWhere('p.productType != :inquiryProductType')
            ->setParameter('inquiryProductType', ProductTypeEnum::TYPE_INQUIRY);

        return $queryBuilder;
    }

    public function getAllOfferedQueryBuilder(int $domainId, PricingGroup $pricingGroup): QueryBuilder
    {
        $queryBuilder = $this->getAllVisibleQueryBuilder($domainId, $pricingGroup);

        $this->queryBuilderExtender
            ->addOrExtendJoin($queryBuilder, 'p.domains', 'pd', 'pd.domainId = :domainId')
            ->setParameter('domainId', $domainId);

        $queryBuilder
            ->andWhere('pd.calculatedSellingDenied = FALSE');

        return $queryBuilder;
    }

    public function getAllVisibleQueryBuilder(int $domainId, PricingGroup $pricingGroup): QueryBuilder
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
        $queryBuilder->addSelect('pd');

        $this->queryBuilderExtender
            ->addOrExtendJoin($queryBuilder, 'p.domains', 'pd', 'pd.domainId = :domainId')
            ->setParameter('domainId', $domainId);

        $queryBuilder->setParameter('domainId', $domainId);
    }

    public function getListableInCategoryQueryBuilder(
        int $domainId,
        PricingGroup $pricingGroup,
        Category $category,
    ): QueryBuilder {
        $queryBuilder = $this->getAllListableQueryBuilder($domainId, $pricingGroup);
        $this->filterByCategory($queryBuilder, $category, $domainId);

        return $queryBuilder;
    }

    public function getListableForBrandQueryBuilder(
        int $domainId,
        PricingGroup $pricingGroup,
        Brand $brand,
    ): QueryBuilder {
        $queryBuilder = $this->getAllListableQueryBuilder($domainId, $pricingGroup);
        $this->filterByBrand($queryBuilder, $brand);

        return $queryBuilder;
    }

    public function getSellableInCategoryQueryBuilder(
        int $domainId,
        PricingGroup $pricingGroup,
        Category $category,
    ): QueryBuilder {
        $queryBuilder = $this->getAllSellableQueryBuilder($domainId, $pricingGroup);
        $this->filterByCategory($queryBuilder, $category, $domainId);

        return $queryBuilder;
    }

    public function getOfferedInCategoryQueryBuilder(
        int $domainId,
        PricingGroup $pricingGroup,
        Category $category,
    ): QueryBuilder {
        $queryBuilder = $this->getAllOfferedQueryBuilder($domainId, $pricingGroup);
        $this->filterByCategory($queryBuilder, $category, $domainId);

        return $queryBuilder;
    }

    public function getListableBySearchTextQueryBuilder(
        int $domainId,
        PricingGroup $pricingGroup,
        string $locale,
        ?string $searchText,
    ): QueryBuilder {
        $queryBuilder = $this->getAllListableQueryBuilder($domainId, $pricingGroup);

        $this->addTranslation($queryBuilder, $locale);

        $this->productElasticsearchRepository->filterBySearchText($queryBuilder, $searchText);

        return $queryBuilder;
    }

    protected function filterByCategory(QueryBuilder $queryBuilder, Category $category, int $domainId): void
    {
        $queryBuilder->join(
            'p.productCategoryDomains',
            'pcd',
            Join::WITH,
            'pcd.category = :category AND pcd.domainId = :domainId',
        );
        $queryBuilder->setParameter('category', $category);
        $queryBuilder->setParameter('domainId', $domainId);
    }

    protected function filterByBrand(QueryBuilder $queryBuilder, Brand $brand): void
    {
        $queryBuilder->andWhere('p.brand = :brand');
        $queryBuilder->setParameter('brand', $brand);
    }

    public function getById(int $id): Product
    {
        $product = $this->findById($id);

        if ($product === null) {
            throw new ProductNotFoundException('Product with ID ' . $id . ' does not exist.');
        }

        return $product;
    }

    /**
     * @param int[] $ids
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getAllByIds(array $ids): array
    {
        return $this->getProductRepository()->findBy(['id' => $ids]);
    }

    /**
     * @return iterable<array{id: int}>
     */
    public function iterateAllProductIdsExceptVariant(): iterable
    {
        return $this->getAllProductsQueryBuilder()
            ->select('p.id')
            ->where('p.variantType != :variantType')
            ->setParameter('variantType', Product::VARIANT_TYPE_VARIANT)
            ->getQuery()
            ->toIterable();
    }

    protected function getAllProductsQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p');
    }

    public function getSellableById(
        int $id,
        int $domainId,
        PricingGroup $pricingGroup,
    ): Product {
        $qb = $this->getAllSellableQueryBuilder($domainId, $pricingGroup);
        $qb->andWhere('p.id = :productId');
        $qb->setParameter('productId', $id);

        $product = $qb->getQuery()->getOneOrNullResult();

        if ($product === null) {
            throw new ProductNotFoundException();
        }

        return $product;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getAllSellableVariantsByMainVariant(
        Product $mainVariant,
        int $domainId,
        PricingGroup $pricingGroup,
    ): array {
        $queryBuilder = $this->getAllSellableQueryBuilder($domainId, $pricingGroup);
        $queryBuilder
            ->andWhere('p.mainVariant = :mainVariant')
            ->setParameter('mainVariant', $mainVariant);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param int[] $sortedProductIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getOfferedByIds(int $domainId, PricingGroup $pricingGroup, array $sortedProductIds): array
    {
        if (count($sortedProductIds) === 0) {
            return [];
        }

        $queryBuilder = $this->getAllOfferedQueryBuilder($domainId, $pricingGroup);
        $queryBuilder
            ->andWhere('p.id IN (:productIds)')
            ->setParameter('productIds', $sortedProductIds)
            ->addSelect('field(p.id, ' . implode(',', $sortedProductIds) . ') AS HIDDEN relevance')
            ->orderBy('relevance');

        return $queryBuilder->getQuery()->getResult();
    }

    public function getOneByUuid(string $uuid): Product
    {
        $product = $this->getProductRepository()->findOneBy(['uuid' => $uuid]);

        if ($product === null) {
            throw new ProductNotFoundException('Product with UUID ' . $uuid . ' does not exist.');
        }

        return $product;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getAllOfferedProductsPaginated(
        int $domainId,
        PricingGroup $pricingGroup,
        int $offset,
        int $limit,
    ): array {
        return $this->getAllOfferedQueryBuilder($domainId, $pricingGroup)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string[] $catnums
     * @return int[]
     */
    public function getProductIdsByCatnums(array $catnums): array
    {
        $result = $this->em->createQueryBuilder()
            ->select('p.id, p.catnum')
            ->from(Product::class, 'p')
            ->where('p.catnum IN (:catnums)')
            ->setParameter('catnums', $catnums)
            ->getQuery()
            ->getScalarResult();

        $catnumToIdMap = array_column($result, 'id', 'catnum');

        return $this->getSortedProductIds($catnums, $catnumToIdMap);
    }

    /**
     * @param string[] $catnumsToSortBy
     * @param array<string, int> $catnumToIdMap
     * @return int[]
     */
    protected function getSortedProductIds(array $catnumsToSortBy, array $catnumToIdMap): array
    {
        $sortedProductIds = [];

        foreach ($catnumsToSortBy as $catnum) {
            if (array_key_exists($catnum, $catnumToIdMap)) {
                $sortedProductIds[] = $catnumToIdMap[$catnum];
            }
        }

        return $sortedProductIds;
    }

    public function findByCatnum(string $catnum): ?Product
    {
        $queryBuilder = $this->getProductRepository()
            ->createQueryBuilder('p')
            ->andWhere('p.catnum = :catnum')
            ->setParameter('catnum', $catnum);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * @param string[] $catnums
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
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
     * @return array<int, bool>
     */
    public function getCalculatedSellingDeniedPerDomainIds(Product $product): array
    {
        $queryBuilder = $this->getProductRepository()
            ->createQueryBuilder('p')
            ->select('pd.domainId, pd.calculatedSellingDenied')
            ->join('p.domains', 'pd')
            ->where('p = :product')
            ->setParameter('product', $product)
            ->orderBy('pd.domainId');

        return array_column($queryBuilder->getQuery()->getArrayResult(), 'calculatedSellingDenied', 'domainId');
    }
}
