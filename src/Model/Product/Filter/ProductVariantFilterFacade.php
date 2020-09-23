<?php

declare(strict_types=1);

namespace App\Model\Product\Filter;

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
            $rank += $this->evaluateByCatnumAndSearchText($variantParameterSetup['variant_catnum'] ?? '', $productFilterData->getSearchText());

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
                    if (isset($variantParameterSetup['variant_availability_info']['product_availability_information'])) {
                        $listedProductView->setAvailability($variantParameterSetup['variant_availability_info']['product_availability_information']);
                    }
                    $listedProductView->setProductAvailableStocksCountInformation(
                        $variantParameterSetup['variant_availability_info']['product_available_stocks_count_information'] ?? null
                    );
                    $listedProductView->setFlagIds($variantParameterSetup['variant_flags'] ?? []);
                    $listedProductView->setHasScontoFlag($variantParameterSetup['variant_has_sconto_flag'] ?? false);
                    $listedProductView->setProductCountExposedInStores($variantParameterSetup['variant_availability_info']['product_count_exposed_in_stores_information'] ?? null);
                }
            }
        }
    }

    /**
     * @param string $catnum
     * @param string|null $searchText
     * @return int
     */
    private function evaluateByCatnumAndSearchText(string $catnum, ?string $searchText): int
    {
        $rank = 0;
        if ($catnum !== '' && $catnum === $searchText) {
            $rank = 100;
        }

        return $rank;
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
