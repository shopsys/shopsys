<?php

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer;

class ProductFilterConfigFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoiceRepository
     */
    private $parameterFilterChoiceRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\FlagFilterChoiceRepository
     */
    private $flagFilterChoiceRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer
     */
    private $currentCustomer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\BrandFilterChoiceRepository
     */
    private $brandFilterChoiceRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRangeRepository
     */
    private $priceRangeRepository;

    public function __construct(
        ParameterFilterChoiceRepository $parameterFilterChoiceRepository,
        FlagFilterChoiceRepository $flagFilterChoiceRepository,
        CurrentCustomer $currentCustomer,
        BrandFilterChoiceRepository $brandFilterChoiceRepository,
        PriceRangeRepository $priceRangeRepository
    ) {
        $this->parameterFilterChoiceRepository = $parameterFilterChoiceRepository;
        $this->flagFilterChoiceRepository = $flagFilterChoiceRepository;
        $this->currentCustomer = $currentCustomer;
        $this->brandFilterChoiceRepository = $brandFilterChoiceRepository;
        $this->priceRangeRepository = $priceRangeRepository;
    }
    
    public function createForCategory(int $domainId, string $locale, Category $category): \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
    {
        $pricingGroup = $this->currentCustomer->getPricingGroup();
        $parameterFilterChoices = $this->parameterFilterChoiceRepository
            ->getParameterFilterChoicesInCategory($domainId, $pricingGroup, $locale, $category);
        $flagFilterChoices = $this->flagFilterChoiceRepository
            ->getFlagFilterChoicesInCategory($domainId, $pricingGroup, $locale, $category);
        $brandFilterChoices = $this->brandFilterChoiceRepository
            ->getBrandFilterChoicesInCategory($domainId, $pricingGroup, $category);
        $priceRange = $this->priceRangeRepository->getPriceRangeInCategory($domainId, $pricingGroup, $category);

        return new ProductFilterConfig($parameterFilterChoices, $flagFilterChoices, $brandFilterChoices, $priceRange);
    }

    public function createForSearch(int $domainId, string $locale, ?string $searchText): \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
    {
        $parameterFilterChoices = [];
        $pricingGroup = $this->currentCustomer->getPricingGroup();
        $flagFilterChoices = $this->flagFilterChoiceRepository
            ->getFlagFilterChoicesForSearch($domainId, $pricingGroup, $locale, $searchText);
        $brandFilterChoices = $this->brandFilterChoiceRepository
            ->getBrandFilterChoicesForSearch($domainId, $pricingGroup, $locale, $searchText);
        $priceRange = $this->priceRangeRepository->getPriceRangeForSearch($domainId, $pricingGroup, $locale, $searchText);

        return new ProductFilterConfig($parameterFilterChoices, $flagFilterChoices, $brandFilterChoices, $priceRange);
    }
}
