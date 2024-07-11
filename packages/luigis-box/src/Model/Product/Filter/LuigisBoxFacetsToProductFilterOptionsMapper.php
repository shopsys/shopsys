<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice;
use Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsFactory;
use Shopsys\LuigisBoxBundle\Model\Brand\BrandRepository;
use Shopsys\LuigisBoxBundle\Model\Flag\FlagRepository;
use Shopsys\LuigisBoxBundle\Model\Product\Filter\Execption\ValueIsNotSliderFormatException;
use Shopsys\LuigisBoxBundle\Model\Product\Parameter\Value\ParameterValueRepository;

class LuigisBoxFacetsToProductFilterOptionsMapper
{
    public const string FACET_AVAILABILITY = 'availability_rank_text';
    public const string FACET_BRAND = 'brand';
    public const string FACET_LABELS = 'labels';
    public const string FACET_PRICE = 'price_amount';
    public const array PRODUCT_FACET_NAMES = [
        self::FACET_AVAILABILITY,
        self::FACET_BRAND,
        self::FACET_LABELS,
        self::FACET_PRICE,
    ];

    /**
     * @param \Shopsys\LuigisBoxBundle\Model\Brand\BrandRepository $brandRepository
     * @param \Shopsys\LuigisBoxBundle\Model\Flag\FlagRepository $flagRepository
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsFactory $productFilterOptionsFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory $productFilterConfigFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\LuigisBoxBundle\Model\Product\Parameter\Value\ParameterValueRepository $parameterValueRepository
     */
    public function __construct(
        protected readonly BrandRepository $brandRepository,
        protected readonly FlagRepository $flagRepository,
        protected readonly ProductFilterOptionsFactory $productFilterOptionsFactory,
        protected readonly ProductFilterConfigFactory $productFilterConfigFactory,
        protected readonly ParameterFacade $parameterFacade,
        protected readonly Domain $domain,
        protected readonly ParameterValueRepository $parameterValueRepository,
    ) {
    }

    /**
     * @param array<int, array<string, mixed>> $luigisBoxFacets
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions
     */
    public function map(array $luigisBoxFacets, ProductFilterData $productFilterData): ProductFilterOptions
    {
        $brands = [];
        $flags = [];
        $parameterFilterChoices = [];
        $priceRange = new PriceRange(Money::zero(), Money::zero());

        $productFilterCountData = new ProductFilterCountData();

        foreach ($luigisBoxFacets as $facetData) {
            if ($facetData['name'] === static::FACET_AVAILABILITY) {
                $this->mapAvailability($facetData, $productFilterCountData);
            }

            if ($facetData['name'] === static::FACET_BRAND) {
                $brands = $this->mapBrands($facetData, $productFilterCountData);
            }

            if ($facetData['name'] === static::FACET_LABELS) {
                $flags = $this->mapFlags($facetData, $productFilterCountData);
            }

            if ($facetData['name'] === static::FACET_PRICE) {
                $priceRange = $this->mapPriceRange($facetData);
            }

            if (in_array($facetData['name'], static::PRODUCT_FACET_NAMES, true)) {
                continue;
            }

            $parameterFilterChoice = $this->mapParameterWithValues($facetData, $productFilterCountData);

            if ($parameterFilterChoice !== null) {
                $parameterFilterChoices[] = $parameterFilterChoice;
            }
        }

        $this->sortParameterChoicesByParameterOrderingPriority($parameterFilterChoices);

        $productFilterConfig = $this->productFilterConfigFactory->create($parameterFilterChoices, $flags, $brands, $priceRange);

        return $this->productFilterOptionsFactory->createFullProductFilterOptions(
            $productFilterConfig,
            $productFilterCountData,
            $productFilterData,
        );
    }

    /**
     * @param array<string, mixed> $facetValues
     * @return array
     */
    protected function mapValuesToCountsByName(array $facetValues): array
    {
        $valuesToCountsByName = [];

        foreach ($facetValues as $facetValue) {
            $valuesToCountsByName[$facetValue['value']] = $facetValue['hits_count'];
        }

        return $valuesToCountsByName;
    }

    /**
     * @param array $facetData
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $productFilterCountData
     */
    protected function mapAvailability(array $facetData, ProductFilterCountData $productFilterCountData): void
    {
        if ($facetData['name'] === self::FACET_AVAILABILITY) {
            $productFilterCountData->countInStock = $facetData['values'][0]['hits_count'] ?? 0;
        }
    }

    /**
     * @param array $facetData
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $productFilterCountData
     * @return array|\Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    protected function mapBrands(
        array $facetData,
        ProductFilterCountData $productFilterCountData,
    ): array {
        $brandCountsByName = $this->mapValuesToCountsByName($facetData['values']);
        $brands = $this->brandRepository->getBrandsByNames(array_keys($brandCountsByName));

        foreach ($brands as $brand) {
            $productFilterCountData->countByBrandId[$brand->getId()] = $brandCountsByName[$brand->getName()];
        }

        return $brands;
    }

    /**
     * @param array $facetData
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $productFilterCountData
     * @return array|\Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    protected function mapFlags(array $facetData, ProductFilterCountData $productFilterCountData): array
    {
        $flagCountsByName = $this->mapValuesToCountsByName($facetData['values']);
        $flags = $this->flagRepository->getFlagsByNames(array_keys($flagCountsByName));

        foreach ($flags as $flag) {
            $productFilterCountData->countByFlagId[$flag->getId()] = $flagCountsByName[$flag->getName()];
        }

        return $flags;
    }

    /**
     * @param array $facetData
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange
     */
    protected function mapPriceRange(array $facetData): PriceRange
    {
        $minPrice = 0;
        $maxPrice = 0;

        foreach ($facetData['values'] as $facetValue) {
            [$minValue, $maxValue] = explode('|', $facetValue['value']);

            if ($minValue < $minPrice || $minPrice === 0) {
                $minPrice = $minValue;
            }

            if ($maxValue > $maxPrice) {
                $maxPrice = $maxValue;
            }
        }

        return new PriceRange(Money::create($minPrice), Money::create($maxPrice));
    }

    /**
     * @param array $facetData
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $productFilterCountData
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice|null
     */
    protected function mapParameterWithValues(
        array $facetData,
        ProductFilterCountData $productFilterCountData,
    ): ?ParameterFilterChoice {
        $parameter = $this->parameterFacade->findParameterByNames([$this->domain->getLocale() => $facetData['name']]);

        if ($parameter === null) {
            return null;
        }

        $isSlider = $parameter->isSlider();
        $parameterValues = [];
        $parameterValueCountsByValue = [];

        if ($isSlider) {
            try {
                $sliderValues = $this->getMinimalAndMaximalValueForSlider($facetData);
                $parameterValues = $this->parameterValueRepository->getSliderParameterValuesForMinAndMaxByLocale([$sliderValues['minimalValue'], $sliderValues['maximalValue']], $this->domain->getLocale());
                $parameterValueCountsByValue = $this->mapValuesToApproximateCountsForSlider($parameterValues, $facetData['values']);
            } catch (ValueIsNotSliderFormatException) {
                $isSlider = false;
            }
        }

        if (!$isSlider) {
            $parameterValueCountsByValue = $this->mapValuesToCountsByName($facetData['values']);

            $parameterValues = $this->parameterValueRepository->getExistingParameterValuesByValuesAndLocale(
                array_keys($parameterValueCountsByValue),
                $this->domain->getLocale(),
            );
        }

        if (count($parameterValues) === 0) {
            return null;
        }

        foreach ($parameterValues as $parameterValue) {
            $productFilterCountData->countByParameterIdAndValueId[$parameter->getId()][$parameterValue->getId()] = $parameterValueCountsByValue[$parameterValue->getText()];
        }

        return new ParameterFilterChoice($parameter, $parameterValues);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[] $parameterFilterChoices
     */
    protected function sortParameterChoicesByParameterOrderingPriority(array &$parameterFilterChoices): void
    {
        usort($parameterFilterChoices, static function (ParameterFilterChoice $a, ParameterFilterChoice $b) {
            $parameterA = $a->getParameter();
            $parameterB = $b->getParameter();

            if ($parameterA->getOrderingPriority() === $parameterB->getOrderingPriority()) {
                return strcmp($parameterA->getName(), $parameterB->getName());
            }

            return $parameterB->getOrderingPriority() - $parameterA->getOrderingPriority();
        });
    }

    /**
     * @param array $facetData
     * @return array{minimalValue: string, maximalValue: string}
     */
    protected function getMinimalAndMaximalValueForSlider(array $facetData): array
    {
        $minimalValue = PHP_INT_MAX;
        $maximalValue = 0;

        foreach ($facetData['values'] as $facetValue) {
            $values = explode('|', $facetValue['value']);

            if (count($values) < 2) {
                throw new ValueIsNotSliderFormatException();
            }

            [$minValue, $maxValue] = $values;

            if ($minValue < $minimalValue) {
                $minimalValue = $minValue;
            }

            if ($maxValue > $maximalValue) {
                $maximalValue = $maxValue;
            }
        }

        return ['minimalValue' => (string)$minimalValue, 'maximalValue' => (string)$maximalValue];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] $parameterValues
     * @param array<string, mixed> $facetValues
     * @return array
     */
    protected function mapValuesToApproximateCountsForSlider(array $parameterValues, array $facetValues): array
    {
        $valuesToCountsByName = [];

        foreach ($parameterValues as $parameterValue) {
            $closestHitsCount = 0;
            $smallestDiff = PHP_INT_MAX;
            $currentValue = (float)$parameterValue->getText();

            foreach ($facetValues as $facetValue) {
                $splitValues = explode('|', $facetValue['value']);
                $from = (float)$splitValues[0];
                $to = (float)$splitValues[1];

                $fromDiff = abs($from - $currentValue);
                $toDiff = abs($to - $currentValue);
                $currentDiff = min($fromDiff, $toDiff);

                if ($currentDiff >= $smallestDiff) {
                    continue;
                }

                $smallestDiff = $currentDiff;
                $closestHitsCount = $facetValue['hits_count'];
            }

            $valuesToCountsByName[$parameterValue->getText()] = $closestHitsCount;
        }

        return $valuesToCountsByName;
    }
}
