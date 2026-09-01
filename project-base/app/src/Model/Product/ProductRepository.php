<?php

declare(strict_types=1);

namespace App\Model\Product;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository as BaseProductRepository;

/**
 * @property \App\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository, \App\Component\Doctrine\QueryBuilderExtender $queryBuilderExtender)
 * @method \App\Model\Product\Product|null findById(int $id)
 * @method \Doctrine\ORM\QueryBuilder getListableInCategoryQueryBuilder(int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, \App\Model\Category\Category $category)
 * @method \Doctrine\ORM\QueryBuilder getListableForBrandQueryBuilder(int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, \App\Model\Product\Brand\Brand $brand)
 * @method \Doctrine\ORM\QueryBuilder getSellableInCategoryQueryBuilder(int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, \App\Model\Category\Category $category)
 * @method \Doctrine\ORM\QueryBuilder getOfferedInCategoryQueryBuilder(int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, \App\Model\Category\Category $category)
 * @method void filterByCategory(\Doctrine\ORM\QueryBuilder $queryBuilder, \App\Model\Category\Category $category, int $domainId)
 * @method void filterByBrand(\Doctrine\ORM\QueryBuilder $queryBuilder, \App\Model\Product\Brand\Brand $brand)
 * @method \App\Model\Product\Product getById(int $id)
 * @method \App\Model\Product\Product[] getAllByIds(int[] $ids)
 * @method \App\Model\Product\Product getSellableById(int $id, int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @method \App\Model\Product\Product[] getOfferedByIds(int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, int[] $sortedProductIds)
 * @method \App\Model\Product\Product getOneByUuid(string $uuid)
 * @method \App\Model\Product\Product[] getAllSellableVariantsByMainVariant(\App\Model\Product\Product $mainVariant, int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @method \App\Model\Product\Product[] getAllOfferedProductsPaginated(int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, int $offset, int $limit)
 * @property \App\Component\Doctrine\QueryBuilderExtender $queryBuilderExtender
 * @method \App\Model\Product\Product|null findByCatnum(string $catnum)
 * @method \App\Model\Product\Product[] findAllByCatnums(string[] $catnums)
 * @method array<int, bool> getCalculatedSellingDeniedPerDomainIds(\App\Model\Product\Product $product)
 */
class ProductRepository extends BaseProductRepository
{
    public function getSellableBySearchTextQueryBuilder(
        int $domainId,
        PricingGroup $pricingGroup,
        string $locale,
        ?string $searchText,
    ): QueryBuilder {
        $queryBuilder = $this->getAllSellableQueryBuilder($domainId, $pricingGroup);

        $this->addTranslation($queryBuilder, $locale);

        $this->productElasticsearchRepository->filterBySearchText($queryBuilder, $searchText);

        return $queryBuilder;
    }
}
