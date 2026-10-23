<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;

class ProductFilterConfigFactory
{
    public function __construct(
        protected readonly ParameterFacade $parameterFacade,
        protected readonly FlagFacade $flagFacade,
        protected readonly BrandFacade $brandFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[] $parameterChoices
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[] $flagChoices
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[] $brandChoices
     */
    public function create(
        array $parameterChoices,
        array $flagChoices,
        array $brandChoices,
        PriceRange $priceRange,
    ): ProductFilterConfig {
        return new ProductFilterConfig($parameterChoices, $flagChoices, $brandChoices, $priceRange);
    }

    public function createWithParameterFlagAndBrandChoices(
        ProductFilterConfigIdsData $productFilterConfigIdsData,
        string $locale,
        Category $category,
    ): ProductFilterConfig {
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

    public function createWithFlagChoices(
        ProductFilterConfigIdsData $productFilterConfigIdsData,
        string $locale,
    ): ProductFilterConfig {
        return $this->create(
            [],
            $this->flagFacade->getVisibleFlagsByIds($productFilterConfigIdsData->getFlagIds(), $locale),
            [],
            $productFilterConfigIdsData->getPriceRange(),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[] $aggregatedParameterFilterChoices
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

    public function createWithFlagAndBrandChoices(
        ProductFilterConfigIdsData $productFilterConfigIdsData,
        string $locale,
    ): ProductFilterConfig {
        return $this->create(
            [],
            $this->flagFacade->getVisibleFlagsByIds($productFilterConfigIdsData->getFlagIds(), $locale),
            $this->brandFacade->getBrandsByIds($productFilterConfigIdsData->getBrandIds()),
            $productFilterConfigIdsData->getPriceRange(),
        );
    }
}
