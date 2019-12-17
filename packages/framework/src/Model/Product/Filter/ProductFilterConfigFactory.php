<?php

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;

class ProductFilterConfigFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoiceRepository
     */
    protected $parameterFilterChoiceRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\FlagFilterChoiceRepository
     */
    protected $flagFilterChoiceRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    protected $currentCustomerUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\BrandFilterChoiceRepository
     */
    protected $brandFilterChoiceRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRangeRepository
     */
    protected $priceRangeRepository;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoiceRepository $parameterFilterChoiceRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\FlagFilterChoiceRepository $flagFilterChoiceRepository
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\BrandFilterChoiceRepository $brandFilterChoiceRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRangeRepository $priceRangeRepository
     */
    public function __construct(
        ParameterFilterChoiceRepository $parameterFilterChoiceRepository,
        FlagFilterChoiceRepository $flagFilterChoiceRepository,
        CurrentCustomerUser $currentCustomerUser,
        BrandFilterChoiceRepository $brandFilterChoiceRepository,
        PriceRangeRepository $priceRangeRepository
    ) {
        $this->parameterFilterChoiceRepository = $parameterFilterChoiceRepository;
        $this->flagFilterChoiceRepository = $flagFilterChoiceRepository;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->brandFilterChoiceRepository = $brandFilterChoiceRepository;
        $this->priceRangeRepository = $priceRangeRepository;
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
        $priceRange = $this->priceRangeRepository->getPriceRangeForSearch($domainId, $pricingGroup, $locale, $searchText);

        return new ProductFilterConfig($parameterFilterChoices, $flagFilterChoices, $brandFilterChoices, $priceRange);
    }
}
