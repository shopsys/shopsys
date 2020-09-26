<?php

declare(strict_types=1);

namespace App\Model\Product\Filter;

use App\Component\Doctrine\QueryBuilderExtender;
use App\Model\Product\ProductRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange;
use Shopsys\FrameworkBundle\Model\Product\Filter\PriceRangeRepository as BasePriceRangeRepository;

/**
 * @property \App\Component\Doctrine\QueryBuilderExtender $queryBuilderExtender
 * @property \App\Model\Product\ProductRepository $productRepository
 */
class PriceRangeRepository extends BasePriceRangeRepository
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    private $currencyFacade;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \App\Component\Doctrine\QueryBuilderExtender $queryBuilderExtender
     */
    public function __construct(
        CurrencyFacade $currencyFacade,
        Domain $domain,
        ProductRepository $productRepository,
        QueryBuilderExtender $queryBuilderExtender
    ) {
        parent::__construct($productRepository, $queryBuilderExtender);
        $this->currencyFacade = $currencyFacade;
        $this->domain = $domain;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $productsQueryBuilder
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange
     */
    protected function getPriceRangeByProductsQueryBuilder(QueryBuilder $productsQueryBuilder, PricingGroup $pricingGroup): PriceRange
    {
        $queryBuilder = clone $productsQueryBuilder;

        $this->queryBuilderExtender
            ->addOrExtendJoin($queryBuilder, 'p.domains', 'pd', 'pd.product = p AND pd.domainId = prv.domainId')
            ->resetDQLPart('groupBy')
            ->resetDQLPart('orderBy')
            ->select('MIN(pd.sellingPriceWithVat) AS minimalPrice, MAX(pd.sellingPriceWithVat) AS maximalPrice');

        $priceRangeData = $queryBuilder->getQuery()->execute();
        $priceRangeDataRow = reset($priceRangeData);

        $minimalPrice = $priceRangeDataRow['minimalPrice'] ?? 0;
        $maximalPrice = $priceRangeDataRow['maximalPrice'] ?? 0;

        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId());
        $minFractionDigits = $currency->getMinFractionDigits();

        return new PriceRange(
            Money::createFromFloat(floor($minimalPrice), $minFractionDigits),
            Money::createFromFloat(ceil($maximalPrice), $minFractionDigits)
        );
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param \App\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange
     */
    public function getPriceRangeInCategory($domainId, PricingGroup $pricingGroup, Category $category)
    {
        $productsQueryBuilder = $this->productRepository->getSellableInCategoryQueryBuilder(
            $domainId,
            $pricingGroup,
            $category
        );

        return $this->getPriceRangeByProductsQueryBuilder($productsQueryBuilder, $pricingGroup);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param string $locale
     * @param string|null $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange
     */
    public function getPriceRangeForSearch($domainId, PricingGroup $pricingGroup, $locale, $searchText)
    {
        $productsQueryBuilder = $this->productRepository
            ->getSellableBySearchTextQueryBuilder($domainId, $pricingGroup, $locale, $searchText);

        return $this->getPriceRangeByProductsQueryBuilder($productsQueryBuilder, $pricingGroup);
    }
}
