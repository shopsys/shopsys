<?php

declare(strict_types=1);

namespace App\Model\CategorySeo;

use App\Model\Category\Category;
use App\Model\Product\Flag\Flag;
use App\Model\Product\Flag\FlagFacade;
use App\Model\Product\Listing\ProductListOrderingModeForListFacade;
use App\Model\Product\Parameter\ParameterRepository;
use App\Model\Product\Parameter\ParameterValue;

class CategorySeoFacade
{
    /**
     * @var \App\Model\Product\Parameter\ParameterRepository
     */
    private $parameterRepository;

    /**
     * @var \App\Model\Product\Flag\FlagFacade
     */
    private $flagFacade;

    /**
     * @var \App\Model\Product\Listing\ProductListOrderingModeForListFacade
     */
    private $productListOrderingModeForListFacade;

    /**
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \App\Model\Product\Flag\FlagFacade $flagFacade
     * @param \App\Model\Product\Listing\ProductListOrderingModeForListFacade $productListOrderingModeForListFacade
     */
    public function __construct(
        ParameterRepository $parameterRepository,
        FlagFacade $flagFacade,
        ProductListOrderingModeForListFacade $productListOrderingModeForListFacade
    ) {
        $this->parameterRepository = $parameterRepository;
        $this->flagFacade = $flagFacade;
        $this->productListOrderingModeForListFacade = $productListOrderingModeForListFacade;
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param int $domainId
     * @return \App\Model\Product\Parameter\Parameter[]
     */
    public function getParametersUsedByProductsInCategory(Category $category, int $domainId): array
    {
        return $this->parameterRepository->getParametersUsedByProductsInCategory($category, $domainId);
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param int $domainId
     * @return \App\Model\Product\Parameter\Parameter[]
     */
    public function getParametersUsedByProductsInCategoryWithoutSlider(Category $category, int $domainId): array
    {
        return $this->parameterRepository->getParametersUsedByProductsInCategoryWithoutSlider($category, $domainId);
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param \App\Model\CategorySeo\CategorySeoFiltersData $categorySeoFiltersData
     * @param int $domainId
     * @param string $locale
     * @return \App\Model\CategorySeo\CategorySeoMix[]
     */
    public function getCategorySeoMixes(
        Category $category,
        CategorySeoFiltersData $categorySeoFiltersData,
        int $domainId,
        string $locale
    ): array {
        $categorySeoMixes = [new CategorySeoMix($domainId, $category)];

        $categorySeoMixes = $this->getSeoCategoryMixesFromParameters(
            $categorySeoMixes,
            $category,
            $categorySeoFiltersData,
            $domainId,
            $locale
        );

        $categorySeoMixes = $this->getSeoCategoryMixesFromFlags(
            $categorySeoMixes,
            $categorySeoFiltersData
        );

        $categorySeoMixes = $this->getSeoCategoryMixesFromOrderings(
            $categorySeoMixes,
            $categorySeoFiltersData
        );

        return $categorySeoMixes;
    }

    /**
     * @param \App\Model\CategorySeo\CategorySeoMix[] $categorySeoMixes
     * @param \App\Model\Category\Category $category
     * @param \App\Model\CategorySeo\CategorySeoFiltersData $categorySeoFiltersData
     * @param int $domainId
     * @param string $locale
     * @return \App\Model\CategorySeo\CategorySeoMix[]
     */
    private function getSeoCategoryMixesFromParameters(
        array $categorySeoMixes,
        Category $category,
        CategorySeoFiltersData $categorySeoFiltersData,
        int $domainId,
        string $locale
    ) {
        foreach ($categorySeoFiltersData->parameters as $parameter) {
            $parameterValues = $this->parameterRepository->getParameterValuesUsedByProductsInCategoryByParameter(
                $category,
                $parameter,
                $domainId,
                $locale
            );

            $categorySeoMixes = $this->getNewSeoCategoryMixes(
                $categorySeoMixes,
                $parameterValues,
                function (CategorySeoMix $categorySeoMix, ParameterValue $parameterValue) {
                    $categorySeoMix->addParameterValue($parameterValue);
                }
            );
        }

        return $categorySeoMixes;
    }

    /**
     * @param \App\Model\CategorySeo\CategorySeoMix[] $categorySeoMixes
     * @param \App\Model\CategorySeo\CategorySeoFiltersData $categorySeoFiltersData
     * @return \App\Model\CategorySeo\CategorySeoMix[]
     */
    private function getSeoCategoryMixesFromFlags(
        array $categorySeoMixes,
        CategorySeoFiltersData $categorySeoFiltersData
    ) {
        if ($categorySeoFiltersData->useFlags === true) {
            $categorySeoMixes = $this->getNewSeoCategoryMixes(
                $categorySeoMixes,
                $this->flagFacade->getAll(),
                function (CategorySeoMix $categorySeoMix, Flag $flag) {
                    $categorySeoMix->setFlag($flag);
                }
            );
        }

        return $categorySeoMixes;
    }

    /**
     * @param \App\Model\CategorySeo\CategorySeoMix[] $categorySeoMixes
     * @param \App\Model\CategorySeo\CategorySeoFiltersData $categorySeoFiltersData
     * @return \App\Model\CategorySeo\CategorySeoMix[]
     */
    private function getSeoCategoryMixesFromOrderings(
        array $categorySeoMixes,
        CategorySeoFiltersData $categorySeoFiltersData
    ) {
        if ($categorySeoFiltersData->useOrdering === true) {
            $orderings = array_keys($this->productListOrderingModeForListFacade
                ->getProductListOrderingConfig()
                ->getSupportedOrderingModesNamesIndexedById());

            $categorySeoMixes = $this->getNewSeoCategoryMixes(
                $categorySeoMixes,
                $orderings,
                function (CategorySeoMix $categorySeoMix, string $ordering) {
                    $categorySeoMix->setOrdering($ordering);
                }
            );
        }

        return $categorySeoMixes;
    }

    /**
     * @param \App\Model\CategorySeo\CategorySeoMix[] $categorySeoMixes
     * @param object[]|string[] $newValues
     * @param callable $categorySeoMixCallback
     * @return \App\Model\CategorySeo\CategorySeoMix[]
     */
    private function getNewSeoCategoryMixes(array $categorySeoMixes, array $newValues, callable $categorySeoMixCallback)
    {
        $newCategorySeoMixes = [];

        foreach ($newValues as $newValue) {
            foreach ($categorySeoMixes as $categorySeoMix) {
                $clonedCategorySeoMix = clone $categorySeoMix;
                $categorySeoMixCallback($clonedCategorySeoMix, $newValue);

                $newCategorySeoMixes[] = $clonedCategorySeoMix;
            }
        }

        return $newCategorySeoMixes;
    }
}
