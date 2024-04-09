<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;

class ProductFilterConfigFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoiceRepository $parameterFilterChoiceRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\FlagFilterChoiceRepository $flagFilterChoiceRepository
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\BrandFilterChoiceRepository $brandFilterChoiceRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRangeRepository $priceRangeRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterElasticFacade $productFilterElasticFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     */
    public function __construct(
        protected readonly ParameterFilterChoiceRepository $parameterFilterChoiceRepository,
        protected readonly FlagFilterChoiceRepository $flagFilterChoiceRepository,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly BrandFilterChoiceRepository $brandFilterChoiceRepository,
        protected readonly PriceRangeRepository $priceRangeRepository,
        protected readonly ProductFilterElasticFacade $productFilterElasticFacade,
        protected readonly ParameterFacade $parameterFacade,
        protected readonly FlagFacade $flagFacade,
        protected readonly BrandFacade $brandFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[] $parameterChoices
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[] $flagChoices
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[] $brandChoices
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange $priceRange
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function create(
        array $parameterChoices,
        array $flagChoices,
        array $brandChoices,
        PriceRange $priceRange,
    ): ProductFilterConfig {
        return new ProductFilterConfig($parameterChoices, $flagChoices, $brandChoices, $priceRange);
    }

    /**
     * @param string $locale
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function createForCategory(
        string $locale,
        Category $category,
    ): ProductFilterConfig {
        $productFilterConfigIdsData = $this->productFilterElasticFacade->getProductFilterDataInCategory(
            $category->getId(),
            $this->currentCustomerUser->getPricingGroup(),
        );

        $aggregatedParameterFilterChoices = $this->parameterFacade->getParameterFilterChoicesByIds(
            $productFilterConfigIdsData->getParameterValueIdsByParameterId(),
            $locale,
        );

        return $this->create(
            $this->getSortedParameterFilterChoicesForCategory($aggregatedParameterFilterChoices, $category),
            $this->flagFacade->getVisibleFlagsByIds($productFilterConfigIdsData->getFlagIds(), $locale),
            $this->brandFacade->getBrandsByIds($productFilterConfigIdsData->getBrandIds()),
            $productFilterConfigIdsData->getPriceRange(),
        );
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param string|null $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function createForSearch(int $domainId, string $locale, ?string $searchText): ProductFilterConfig
    {
        $parameterFilterChoices = [];
        $pricingGroup = $this->currentCustomerUser->getPricingGroup();
        $flagFilterChoices = $this->flagFilterChoiceRepository
            ->getFlagFilterChoicesForSearch($domainId, $pricingGroup, $locale, $searchText);
        $brandFilterChoices = $this->brandFilterChoiceRepository
            ->getBrandFilterChoicesForSearch($domainId, $pricingGroup, $locale, $searchText);
        $priceRange = $this->priceRangeRepository->getPriceRangeForSearch(
            $domainId,
            $pricingGroup,
            $locale,
            $searchText,
        );

        return $this->create($parameterFilterChoices, $flagFilterChoices, $brandFilterChoices, $priceRange);
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function createForBrand(int $domainId, string $locale, Brand $brand): ProductFilterConfig
    {
        $pricingGroup = $this->currentCustomerUser->getPricingGroup();
        $flagFilterChoices = $this->flagFilterChoiceRepository
            ->getFlagFilterChoicesForBrand($domainId, $pricingGroup, $locale, $brand);
        $priceRange = $this->priceRangeRepository->getPriceRangeForBrand($domainId, $pricingGroup, $brand);

        return $this->create([], $flagFilterChoices, [], $priceRange);
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function createForAll(int $domainId, string $locale): ProductFilterConfig
    {
        $pricingGroup = $this->currentCustomerUser->getPricingGroup();
        $flagFilterChoices = $this->flagFilterChoiceRepository
            ->getFlagFilterChoicesForAll($domainId, $pricingGroup, $locale);
        $priceRange = $this->priceRangeRepository->getPriceRangeForAll($domainId, $pricingGroup);
        $brandFilterChoices = $this->brandFilterChoiceRepository
            ->getBrandFilterChoicesForAll($domainId, $pricingGroup);

        return $this->create([], $flagFilterChoices, $brandFilterChoices, $priceRange);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[] $aggregatedParameterFilterChoices
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[]
     */
    protected function getSortedParameterFilterChoicesForCategory(
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
