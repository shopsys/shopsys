<?php

declare(strict_types=1);

namespace App\Model\Product\Filter\Elasticsearch;

use App\Model\Category\Category;
use App\Model\Product\Brand\BrandFacade;
use App\Model\Product\Flag\FlagFacade;
use App\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;

class ProductFilterConfigFactory
{
    /**
     * @var \App\Model\Product\Filter\Elasticsearch\ProductFilterElasticFacade
     */
    private ProductFilterElasticFacade $productFilterElasticFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @var \App\Model\Product\Flag\FlagFacade
     */
    private FlagFacade $flagFacade;

    /**
     * @var \App\Model\Product\Brand\BrandFacade
     */
    private BrandFacade $brandFacade;

    /**
     * @var \App\Model\Product\Parameter\ParameterFacade
     */
    private ParameterFacade $parameterFacade;

    /**
     * @param \App\Model\Product\Filter\Elasticsearch\ProductFilterElasticFacade $productFilterElasticFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Product\Flag\FlagFacade $flagFacade
     * @param \App\Model\Product\Brand\BrandFacade $brandFacade
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     */
    public function __construct(
        ProductFilterElasticFacade $productFilterElasticFacade,
        CurrentCustomerUser $currentCustomerUser,
        FlagFacade $flagFacade,
        BrandFacade $brandFacade,
        ParameterFacade $parameterFacade
    ) {
        $this->productFilterElasticFacade = $productFilterElasticFacade;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->flagFacade = $flagFacade;
        $this->brandFacade = $brandFacade;
        $this->parameterFacade = $parameterFacade;
    }

    /**
     * @param string $locale
     * @param \App\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function createForCategory(string $locale, Category $category): ProductFilterConfig
    {
        $productFilterConfigIdsData = $this->productFilterElasticFacade->getProductFilterDataInCategory(
            $category->getId(),
            $this->currentCustomerUser->getPricingGroup()
        );

        $aggregatedParameterFilterChoices = $this->parameterFacade->getParameterFilterChoicesByIds(
            $productFilterConfigIdsData->getParameterValueIdsByParameterId(),
            $locale
        );

        return new ProductFilterConfig(
            $this->getSortedParameterFilterChoicesForCategory($aggregatedParameterFilterChoices, $category),
            $this->flagFacade->getVisibleFlagsByIds($productFilterConfigIdsData->getFlagIds(), $locale),
            $this->brandFacade->getBrandsByIds($productFilterConfigIdsData->getBrandIds()),
            $productFilterConfigIdsData->getPriceRange()
        );
    }

    /**
     * @param string $locale
     * @param string|null $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function createForSearch(string $locale, ?string $searchText = null): ProductFilterConfig
    {
        $productFilterConfigIdsData = $this->productFilterElasticFacade->getProductFilterDataForSearch(
            $searchText,
            $this->currentCustomerUser->getPricingGroup()
        );

        return new ProductFilterConfig(
            [],
            $this->flagFacade->getVisibleFlagsByIds($productFilterConfigIdsData->getFlagIds(), $locale),
            $this->brandFacade->getBrandsByIds($productFilterConfigIdsData->getBrandIds()),
            $productFilterConfigIdsData->getPriceRange()
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[] $aggregatedParameterFilterChoices
     * @param \App\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[]
     */
    private function getSortedParameterFilterChoicesForCategory(
        array $aggregatedParameterFilterChoices,
        Category $category
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
