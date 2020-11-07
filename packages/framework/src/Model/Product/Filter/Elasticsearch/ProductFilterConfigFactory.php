<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;

class ProductFilterConfigFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    protected $currentCustomerUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\Elasticsearch\ProductFilterElasticFacade
     */
    protected $productFilterElasticFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade
     */
    protected $flagFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade
     */
    protected $brandFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade
     */
    protected $parameterFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\Elasticsearch\ProductFilterElasticFacade $productFilterElasticFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
     */
    public function __construct(
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser,
        ProductFilterElasticFacade $productFilterElasticFacade,
        FlagFacade $flagFacade,
        BrandFacade $brandFacade,
        ParameterFacade $parameterFacade
    ) {
        $this->domain = $domain;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->productFilterElasticFacade = $productFilterElasticFacade;
        $this->flagFacade = $flagFacade;
        $this->brandFacade = $brandFacade;
        $this->parameterFacade = $parameterFacade;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function createForCategory(Category $category): ProductFilterConfig
    {
        $elasticFilterData = $this->productFilterElasticFacade->getProductFilterDataInCategory(
            $category->getId(),
            $this->currentCustomerUser->getPricingGroup()
        );

        return new ProductFilterConfig(
            $this->aggregateParametersData($elasticFilterData),
            $this->aggregateFlagsData($elasticFilterData),
            $this->aggregateBrandsData($elasticFilterData),
            $this->aggregatePriceRangeData($elasticFilterData)
        );
    }

    /**
     * @param string|null $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function createForSearch(?string $searchText = null): ProductFilterConfig
    {
        $elasticFilterData = $this->productFilterElasticFacade->getProductFilterDataForSearch(
            $searchText,
            $this->currentCustomerUser->getPricingGroup()
        );

        return new ProductFilterConfig(
            [],
            $this->aggregateFlagsData($elasticFilterData),
            $this->aggregateBrandsData($elasticFilterData),
            $this->aggregatePriceRangeData($elasticFilterData)
        );
    }

    /**
     * @param array $elasticFilterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange
     */
    protected function aggregatePriceRangeData(array $elasticFilterData): PriceRange
    {
        $pricesData = $elasticFilterData['aggregations']['prices']['filter_pricing_group'];

        $minPrice = Money::create((string)($pricesData['min_price']['value'] ?? 0));
        $maxPrice = Money::create((string)($pricesData['max_price']['value'] ?? 0));

        return new PriceRange($minPrice, $maxPrice);
    }

    /**
     * @param array $elasticFilterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    protected function aggregateFlagsData(array $elasticFilterData): array
    {
        $flagsData = $elasticFilterData['aggregations']['flags']['buckets'];

        $flagsIds = array_map(function (array $data) {
            return $data['key'];
        }, $flagsData);

        if (count($flagsIds) === 0) {
            return [];
        }

        return $this->flagFacade->getFlagsForFilterByIds($flagsIds, $this->domain->getLocale());
    }

    /**
     * @param array $elasticFilterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    protected function aggregateBrandsData(array $elasticFilterData): array
    {
        $brandsData = $elasticFilterData['aggregations']['brands']['buckets'];

        $brandsIds = array_map(function (array $data) {
            return $data['key'];
        }, $brandsData);

        if (count($brandsIds) === 0) {
            return [];
        }

        return $this->brandFacade->getBrandsForFilterByIds($brandsIds, $this->domain->getLocale());
    }

    /**
     * @param array $elasticFilterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[]
     */
    protected function aggregateParametersData(array $elasticFilterData): array
    {
        $parametersData = $elasticFilterData['aggregations']['parameters']['by_parameters']['buckets'];

        $parameterValueIdsIndexedByParameterId = [];

        foreach ($parametersData as $parameter) {
            $parameterValueIdsIndexedByParameterId[$parameter['key']] = array_map(function ($parameter) {
                return $parameter['key'];
            }, $parameter['by_value']['buckets']);
        }

        if (count($parameterValueIdsIndexedByParameterId) === 0) {
            return [];
        }

        return $this->parameterFacade->getParameterFilterChoicesByIds(
            $parameterValueIdsIndexedByParameterId,
            $this->domain->getLocale()
        );
    }
}
