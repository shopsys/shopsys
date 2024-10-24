<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForListFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;

class CategorySeoFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForListFacade $productListOrderingModeForListFacade
     */
    public function __construct(
        protected readonly ParameterRepository $parameterRepository,
        protected readonly FlagFacade $flagFacade,
        protected readonly ProductListOrderingModeForListFacade $productListOrderingModeForListFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public function getParametersUsedByProductsInCategoryWithoutSlider(Category $category, int $domainId): array
    {
        return $this->parameterRepository->getParametersUsedByProductsInCategoryWithoutSlider($category, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoFiltersData $categorySeoFiltersData
     * @param int $domainId
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoMix[]
     */
    public function getCategorySeoMixes(
        Category $category,
        CategorySeoFiltersData $categorySeoFiltersData,
        int $domainId,
        string $locale,
    ): array {
        $categorySeoMixes = [new CategorySeoMix($domainId, $category)];

        $categorySeoMixes = $this->getSeoCategoryMixesFromParameters(
            $categorySeoMixes,
            $category,
            $categorySeoFiltersData,
            $domainId,
            $locale,
        );

        $categorySeoMixes = $this->getSeoCategoryMixesFromFlags(
            $categorySeoMixes,
            $categorySeoFiltersData,
        );

        $categorySeoMixes = $this->getSeoCategoryMixesFromOrderings(
            $categorySeoMixes,
            $categorySeoFiltersData,
        );

        return $categorySeoMixes;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoMix[] $categorySeoMixes
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoFiltersData $categorySeoFiltersData
     * @param int $domainId
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoMix[]
     */
    protected function getSeoCategoryMixesFromParameters(
        array $categorySeoMixes,
        Category $category,
        CategorySeoFiltersData $categorySeoFiltersData,
        int $domainId,
        string $locale,
    ): array {
        foreach ($categorySeoFiltersData->parameters as $parameter) {
            $parameterValues = $this->parameterRepository->getParameterValuesUsedByProductsInCategoryByParameter(
                $category,
                $parameter,
                $domainId,
                $locale,
            );

            $categorySeoMixes = $this->getNewSeoCategoryMixes(
                $categorySeoMixes,
                $parameterValues,
                function (CategorySeoMix $categorySeoMix, ParameterValue $parameterValue) {
                    $categorySeoMix->addParameterValue($parameterValue);
                },
            );
        }

        return $categorySeoMixes;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoMix[] $categorySeoMixes
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoFiltersData $categorySeoFiltersData
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoMix[]
     */
    protected function getSeoCategoryMixesFromFlags(
        array $categorySeoMixes,
        CategorySeoFiltersData $categorySeoFiltersData,
    ): array {
        if ($categorySeoFiltersData->useFlags === true) {
            $categorySeoMixes = $this->getNewSeoCategoryMixes(
                $categorySeoMixes,
                $this->flagFacade->getAll(),
                function (CategorySeoMix $categorySeoMix, Flag $flag) {
                    $categorySeoMix->setFlag($flag);
                },
            );
        }

        return $categorySeoMixes;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoMix[] $categorySeoMixes
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoFiltersData $categorySeoFiltersData
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoMix[]
     */
    protected function getSeoCategoryMixesFromOrderings(
        array $categorySeoMixes,
        CategorySeoFiltersData $categorySeoFiltersData,
    ): array {
        if ($categorySeoFiltersData->useOrdering === true) {
            $orderings = array_keys($this->productListOrderingModeForListFacade
                ->getProductListOrderingConfig()
                ->getSupportedOrderingModesNamesIndexedById());

            $categorySeoMixes = $this->getNewSeoCategoryMixes(
                $categorySeoMixes,
                $orderings,
                function (CategorySeoMix $categorySeoMix, string $ordering) {
                    $categorySeoMix->setOrdering($ordering);
                },
            );
        }

        return $categorySeoMixes;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoMix[] $categorySeoMixes
     * @param object[]|string[] $newValues
     * @param callable $categorySeoMixCallback
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoMix[]
     */
    protected function getNewSeoCategoryMixes(
        array $categorySeoMixes,
        array $newValues,
        callable $categorySeoMixCallback,
    ): array {
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
