<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;

class ProductFilterConfigFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoiceRepository $parameterFilterChoiceRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\FlagFilterChoiceRepository $flagFilterChoiceRepository
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\BrandFilterChoiceRepository $brandFilterChoiceRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRangeRepository $priceRangeRepository
     */
    public function __construct(
        protected readonly ParameterFilterChoiceRepository $parameterFilterChoiceRepository,
        protected readonly FlagFilterChoiceRepository $flagFilterChoiceRepository,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly BrandFilterChoiceRepository $brandFilterChoiceRepository,
        protected readonly PriceRangeRepository $priceRangeRepository,
    ) {
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function createForCategory($domainId, $locale, Category $category)
    {
        $pricingGroup = $this->currentCustomerUser->getPricingGroup();
        $parameterFilterChoices = $this->parameterFilterChoiceRepository
            ->getParameterFilterChoicesInCategory($domainId, $pricingGroup, $locale, $category);
        $flagFilterChoices = $this->flagFilterChoiceRepository
            ->getFlagFilterChoicesInCategory($domainId, $pricingGroup, $locale, $category);
        $brandFilterChoices = $this->brandFilterChoiceRepository
            ->getBrandFilterChoicesInCategory($domainId, $pricingGroup, $category);
        $priceRange = $this->priceRangeRepository->getPriceRangeInCategory($domainId, $pricingGroup, $category);

        return new ProductFilterConfig($parameterFilterChoices, $flagFilterChoices, $brandFilterChoices, $priceRange);
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param string|null $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function createForSearch($domainId, $locale, $searchText)
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

        return new ProductFilterConfig($parameterFilterChoices, $flagFilterChoices, $brandFilterChoices, $priceRange);
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

        return new ProductFilterConfig([], $flagFilterChoices, [], $priceRange);
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

        return new ProductFilterConfig([], $flagFilterChoices, $brandFilterChoices, $priceRange);
    }
}
