<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderExtender;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class PriceRangeRepository
{
    public function __construct(
        protected readonly ProductRepository $productRepository,
        protected readonly QueryBuilderExtender $queryBuilderExtender,
        protected readonly CurrencyFacade $currencyFacade,
    ) {
    }

    public function getPriceRangeInCategory(int $domainId, PricingGroup $pricingGroup, Category $category): PriceRange
    {
        $productsQueryBuilder = $this->productRepository->getListableInCategoryQueryBuilder(
            $domainId,
            $pricingGroup,
            $category,
        );

        return $this->getPriceRangeByProductsQueryBuilder($productsQueryBuilder, $pricingGroup);
    }

    public function getPriceRangeForBrand(int $domainId, PricingGroup $pricingGroup, Brand $brand): PriceRange
    {
        $productsQueryBuilder = $this->productRepository->getListableForBrandQueryBuilder(
            $domainId,
            $pricingGroup,
            $brand,
        );

        return $this->getPriceRangeByProductsQueryBuilder($productsQueryBuilder, $pricingGroup);
    }

    public function getPriceRangeForAll(int $domainId, PricingGroup $pricingGroup): PriceRange
    {
        $productsQueryBuilder = $this->productRepository->getAllListableQueryBuilder(
            $domainId,
            $pricingGroup,
        );

        return $this->getPriceRangeByProductsQueryBuilder($productsQueryBuilder, $pricingGroup);
    }

    public function getPriceRangeForSearch(
        int $domainId,
        PricingGroup $pricingGroup,
        string $locale,
        ?string $searchText,
    ): PriceRange {
        $productsQueryBuilder = $this->productRepository
            ->getListableBySearchTextQueryBuilder($domainId, $pricingGroup, $locale, $searchText);

        return $this->getPriceRangeByProductsQueryBuilder($productsQueryBuilder, $pricingGroup);
    }

    protected function getPriceRangeByProductsQueryBuilder(
        QueryBuilder $productsQueryBuilder,
        PricingGroup $pricingGroup,
    ): PriceRange {
        $queryBuilder = clone $productsQueryBuilder;

        $this->queryBuilderExtender
            ->addOrExtendJoin($queryBuilder, ProductManualInputPrice::class, 'pmip', 'pmip.product = p')
            ->andWhere('pmip.pricingGroup = :pricingGroup')
            ->setParameter('pricingGroup', $pricingGroup)
            ->andWhere('pmip.currency = :currency')
            ->setParameter('currency', $this->currencyFacade->getDomainDefaultCurrencyByDomainId($pricingGroup->getDomainId()))
            ->resetDQLPart('groupBy')
            ->resetDQLPart('orderBy')
            ->select('MIN(pmip.inputPrice) AS minimalPrice, MAX(pmip.inputPrice) AS maximalPrice');

        $priceRangeData = $queryBuilder->getQuery()->getResult();
        $priceRangeDataRow = array_first($priceRangeData);

        return new PriceRange(
            Money::create($priceRangeDataRow['minimalPrice'] ?? 0),
            Money::create($priceRangeDataRow['maximalPrice'] ?? 0),
        );
    }
}
