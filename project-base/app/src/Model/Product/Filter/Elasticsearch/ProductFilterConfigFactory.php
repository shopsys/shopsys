<?php

declare(strict_types=1);

namespace App\Model\Product\Filter\Elasticsearch;

use App\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory as BaseProductFilterConfigFactory;

/**
 * @property \App\Model\Product\Filter\ParameterFilterChoiceRepository $parameterFilterChoiceRepository
 * @property \App\Model\Product\Filter\FlagFilterChoiceRepository $flagFilterChoiceRepository
 * @property \App\Model\Product\Filter\PriceRangeRepository $priceRangeRepository
 * @property \App\Model\Product\Filter\BrandFilterChoiceRepository $brandFilterChoiceRepository
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @property \App\Model\Product\Parameter\ParameterFacade $parameterFacade
 * @property \App\Model\Product\Flag\FlagFacade $flagFacade
 * @method __construct(\App\Model\Product\Filter\ParameterFilterChoiceRepository $parameterFilterChoiceRepository, \App\Model\Product\Filter\FlagFilterChoiceRepository $flagFilterChoiceRepository, \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \App\Model\Product\Filter\BrandFilterChoiceRepository $brandFilterChoiceRepository, \App\Model\Product\Filter\PriceRangeRepository $priceRangeRepository, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterElasticFacade $productFilterElasticFacade, \App\Model\Product\Parameter\ParameterFacade $parameterFacade, \App\Model\Product\Flag\FlagFacade $flagFacade, \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig createForCategory(string $locale, \App\Model\Category\Category $category)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[] getSortedParameterFilterChoicesForCategory(\Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[] $aggregatedParameterFilterChoices, \App\Model\Category\Category $category)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig create(\Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[] $parameterChoices, \App\Model\Product\Flag\Flag[] $flagChoices, \App\Model\Product\Brand\Brand[] $brandChoices, \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange $priceRange)
 */
class ProductFilterConfigFactory extends BaseProductFilterConfigFactory
{
    /**
     * @param int $domainId
     * @param string $locale
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function createForSearch($domainId, $locale, $searchText = ''): ProductFilterConfig
    {
        $productFilterConfigIdsData = $this->productFilterElasticFacade->getProductFilterDataForSearch(
            $searchText,
            $this->currentCustomerUser->getPricingGroup(),
        );

        return new ProductFilterConfig(
            [],
            $this->flagFacade->getVisibleFlagsByIds($productFilterConfigIdsData->getFlagIds(), $locale),
            $this->brandFacade->getBrandsByIds($productFilterConfigIdsData->getBrandIds()),
            $productFilterConfigIdsData->getPriceRange(),
        );
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param \App\Model\Product\Brand\Brand $brand
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function createForBrand(
        int $domainId,
        string $locale,
        Brand $brand,
    ): ProductFilterConfig {
        $productFilterConfigIdsData = $this->productFilterElasticFacade->getProductFilterDataInBrand(
            $brand->getId(),
            $this->currentCustomerUser->getPricingGroup(),
        );

        return new ProductFilterConfig(
            [],
            $this->flagFacade->getVisibleFlagsByIds($productFilterConfigIdsData->getFlagIds(), $locale),
            [],
            $productFilterConfigIdsData->getPriceRange(),
        );
    }

    /**
     * @param \App\Model\Product\Flag\Flag $flag
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function createForFlag(Flag $flag, string $locale): ProductFilterConfig
    {
        $productFilterConfigIdsData = $this->productFilterElasticFacade->getProductFilterDataInFlag(
            $flag->getId(),
            $this->currentCustomerUser->getPricingGroup(),
        );

        return new ProductFilterConfig(
            [],
            $this->flagFacade->getVisibleFlagsByIds($productFilterConfigIdsData->getFlagIds(), $locale),
            $this->brandFacade->getBrandsByIds($productFilterConfigIdsData->getBrandIds()),
            $productFilterConfigIdsData->getPriceRange(),
        );
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function createForAll(int $domainId, string $locale): ProductFilterConfig
    {
        $productFilterConfigIdsData = $this->productFilterElasticFacade->getProductFilterDataForAll(
            $this->currentCustomerUser->getPricingGroup(),
        );

        return new ProductFilterConfig(
            [],
            $this->flagFacade->getVisibleFlagsByIds($productFilterConfigIdsData->getFlagIds(), $locale),
            $this->brandFacade->getBrandsByIds($productFilterConfigIdsData->getBrandIds()),
            $productFilterConfigIdsData->getPriceRange(),
        );
    }
}
