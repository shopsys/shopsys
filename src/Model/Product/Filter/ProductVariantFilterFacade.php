<?php

declare(strict_types=1);

namespace App\Model\Product\Filter;

use App\Model\Product\Filter\ProductFilterData;
use App\Model\Product\Listed\ListedProductView;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

class ProductVariantFilterFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult $paginationResult
     */
    public function setupDefaultVariantsInPaginationResult(PaginationResult $paginationResult): void
    {
        $this->setupMostValuableVariantsInPaginationResultByProductFilterData($paginationResult, new ProductFilterData());
    }

    /**
     * @param \App\Model\Product\Listed\ListedProductView[] $listedProductViews
     */
    public function setupDefaultVariantsInListedProductViews(array $listedProductViews): void
    {
        $productFilterData = new ProductFilterData();
        foreach ($listedProductViews as &$listedProductView) {
            $this->filterMostValuableVariantInListedProductView($listedProductView, $productFilterData);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult $paginationResult
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     */
    public function setupMostValuableVariantsInPaginationResultByProductFilterData(PaginationResult $paginationResult, ProductFilterData $productFilterData): void
    {
        foreach ($paginationResult->getResults() as &$listedProductView) {
            $this->filterMostValuableVariantInListedProductView($listedProductView, $productFilterData);
        }
    }

    /**
     * @param \App\Model\Product\Listed\ListedProductView $listedProductView
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     */
    private function filterMostValuableVariantInListedProductView(ListedProductView $listedProductView, ProductFilterData $productFilterData): void
    {
        $highestRank = 0;
        $mostValuableVariantId = null;
        foreach ($listedProductView->getVariantsParametersSetup() ?? [] as $variantId => $variantParameterSetup) {
            if ($mostValuableVariantId === null) {
                $mostValuableVariantId = $variantId;
            }

            $isDefaultVariant = $variantParameterSetup['is_default_variant'] ?? false;
            if ($highestRank === 0 && $isDefaultVariant) {
                $mostValuableVariantId = $variantId;
            }

            $rank = $this->evaluateParameterValuesSetup($variantParameterSetup['parameter_values_setup'], $productFilterData);

            if (isset($variantParameterSetup['extended_parameter_values_setup'])) {
                $rank += $this->evaluateParameterValuesSetup($variantParameterSetup['extended_parameter_values_setup'], $productFilterData);
            }
            if ($rank > $highestRank) {
                $highestRank = $rank;
                $mostValuableVariantId = $variantId;
            }
        }

        if ($mostValuableVariantId !== null) {
            foreach ($listedProductView->getVariantsParametersSetup() as $variantId => $variantParameterSetup) {
                if ($variantId !== $mostValuableVariantId) {
                    $listedProductView->deleteVariantParametersSetupByVariantId($variantId);
                } else {
                    $listedProductView->setVariantUrl($variantParameterSetup['variant_url'] ?? null);
                    $listedProductView->setVariantImageUrl($variantParameterSetup['image_url'] ?? null);
                }
            }
        }
    }

    /**
     * @param array $parameterValuesSetup
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @return int
     */
    private function evaluateParameterValuesSetup(array $parameterValuesSetup, ProductFilterData $productFilterData): int
    {
        $rank = 0;
        foreach ($productFilterData->parameters as $parameterFilterData) {
            if (count($parameterFilterData->values) === 0) {
                continue;
            }
            $parameterId = $parameterFilterData->parameter->getId();

            foreach ($parameterFilterData->values as $parameterValue) {
                $parameterValueId = $parameterValue->getId();
                $isParameterValueInVariantSetup = $parameterValuesSetup[$parameterId][$parameterValueId] ?? false;
                if ($isParameterValueInVariantSetup !== false) {
                    $rank++;
                }
            }
        }

        return $rank;
    }
}
