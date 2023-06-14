<?php

declare(strict_types=1);

namespace App\Model\Product\Filter\Elasticsearch;

use App\Model\Category\Category;
use App\Model\Product\Brand\BrandFacade;
use App\Model\Product\Flag\Flag;
use App\Model\Product\Flag\FlagFacade;
use App\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrameworkBundle\Model\Category\Category as BaseCategory;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\BrandFilterChoiceRepository;
use Shopsys\FrameworkBundle\Model\Product\Filter\FlagFilterChoiceRepository;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoiceRepository;
use Shopsys\FrameworkBundle\Model\Product\Filter\PriceRangeRepository;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory as BaseProductFilterConfigFactory;

/**
 * @property \App\Model\Product\Filter\ParameterFilterChoiceRepository $parameterFilterChoiceRepository
 * @property \App\Model\Product\Filter\FlagFilterChoiceRepository $flagFilterChoiceRepository
 * @property \App\Model\Product\Filter\PriceRangeRepository $priceRangeRepository
 * @property \App\Model\Product\Filter\BrandFilterChoiceRepository $brandFilterChoiceRepository
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 */
class ProductFilterConfigFactory extends BaseProductFilterConfigFactory
{
    /**
     * @param \App\Model\Product\Filter\ParameterFilterChoiceRepository $parameterFilterChoiceRepository
     * @param \App\Model\Product\Filter\FlagFilterChoiceRepository $flagFilterChoiceRepository
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Product\Filter\BrandFilterChoiceRepository $brandFilterChoiceRepository
     * @param \App\Model\Product\Filter\PriceRangeRepository $priceRangeRepository
     * @param \App\Model\Product\Filter\Elasticsearch\ProductFilterElasticFacade $productFilterElasticFacade
     * @param \App\Model\Product\Flag\FlagFacade $flagFacade
     * @param \App\Model\Product\Brand\BrandFacade $brandFacade
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     */
    public function __construct(
        ParameterFilterChoiceRepository $parameterFilterChoiceRepository,
        FlagFilterChoiceRepository $flagFilterChoiceRepository,
        CurrentCustomerUser $currentCustomerUser,
        BrandFilterChoiceRepository $brandFilterChoiceRepository,
        PriceRangeRepository $priceRangeRepository,
        private readonly ProductFilterElasticFacade $productFilterElasticFacade,
        private readonly FlagFacade $flagFacade,
        private readonly BrandFacade $brandFacade,
        private readonly ParameterFacade $parameterFacade,
    ) {
        parent::__construct(
            $parameterFilterChoiceRepository,
            $flagFilterChoiceRepository,
            $currentCustomerUser,
            $brandFilterChoiceRepository,
            $priceRangeRepository,
        );
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param \App\Model\Category\Category $category
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function createForCategory(
        $domainId,
        $locale,
        BaseCategory $category,
        string $searchText = '',
    ): ProductFilterConfig {
        $productFilterConfigIdsData = $this->productFilterElasticFacade->getProductFilterDataInCategory(
            $category->getId(),
            $this->currentCustomerUser->getPricingGroup(),
            $searchText,
        );

        $aggregatedParameterFilterChoices = $this->parameterFacade->getParameterFilterChoicesByIds(
            $productFilterConfigIdsData->getParameterValueIdsByParameterId(),
            $locale,
        );

        return new ProductFilterConfig(
            $this->getSortedParameterFilterChoicesForCategory($aggregatedParameterFilterChoices, $category),
            $this->flagFacade->getVisibleFlagsByIds($productFilterConfigIdsData->getFlagIds(), $locale),
            $this->brandFacade->getBrandsByIds($productFilterConfigIdsData->getBrandIds()),
            $productFilterConfigIdsData->getPriceRange(),
        );
    }

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
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function createForBrand(
        int $domainId,
        string $locale,
        Brand $brand,
        string $searchText = '',
    ): ProductFilterConfig {
        $productFilterConfigIdsData = $this->productFilterElasticFacade->getProductFilterDataInBrand(
            $brand->getId(),
            $this->currentCustomerUser->getPricingGroup(),
            $searchText,
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
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function createForFlag(Flag $flag, string $locale, string $searchText = ''): ProductFilterConfig
    {
        $productFilterConfigIdsData = $this->productFilterElasticFacade->getProductFilterDataInFlag(
            $flag->getId(),
            $this->currentCustomerUser->getPricingGroup(),
            $searchText,
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[] $aggregatedParameterFilterChoices
     * @param \App\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[]
     */
    private function getSortedParameterFilterChoicesForCategory(
        array $aggregatedParameterFilterChoices,
        Category $category,
    ): array {
        $aggregatedParametersFilterChoicesIndexedByParameterId = [];

        foreach ($aggregatedParameterFilterChoices as $aggregatedParameterFilterChoice) {
            $aggregatedParametersFilterChoicesIndexedByParameterId[$aggregatedParameterFilterChoice->getParameter()->getId()] = $aggregatedParameterFilterChoice;
        }

        $sortedParameterFilterChoices = [];

        foreach ($this->parameterFacade->getParametersIdsSortedByPositionFilteredByCategory($category) as $sortedParameterId) {
            if (!array_key_exists($sortedParameterId, $aggregatedParametersFilterChoicesIndexedByParameterId)) {
                continue;
            }
            $sortedParameterFilterChoices[] = $aggregatedParametersFilterChoicesIndexedByParameterId[$sortedParameterId];
        }

        return $sortedParameterFilterChoices;
    }
}
