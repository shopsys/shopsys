<?php

declare(strict_types=1);

namespace App\Model\Product\Series\Transfer\Akeneo;

use App\Component\Akeneo\Product\AkeneoProductHelper;
use App\Model\Product\Series\ProductSeries;
use App\Model\Product\Series\ProductSeriesData;
use App\Model\Product\Series\ProductSeriesDataFactory;

class ProductSeriesTransferAkeneoMapper
{
    /**
     * @var \App\Model\Product\Series\ProductSeriesDataFactory
     */
    private $productSeriesDataFactory;

    /**
     * @param \App\Model\Product\Series\ProductSeriesDataFactory $productSeriesDataFactory
     */
    public function __construct(ProductSeriesDataFactory $productSeriesDataFactory)
    {
        $this->productSeriesDataFactory = $productSeriesDataFactory;
    }

    /**
     * @param array $akeneoData
     * @param \App\Model\Product\Series\ProductSeries|null $productSeries
     * @return \App\Model\Product\Series\ProductSeriesData
     */
    public function mapAkeneoDataToProductSeriesData(array $akeneoData, ?ProductSeries $productSeries): ProductSeriesData
    {
        if ($productSeries === null) {
            $productSeriesData = $this->productSeriesDataFactory->create();
        } else {
            $productSeriesData = $this->productSeriesDataFactory->createFromProductSeries($productSeries);
        }

        $productSeriesData->akeneoCode = $akeneoData['code'];

        $productSeriesData->name = AkeneoProductHelper::mapLocalizedDataString($productSeriesData->name, $akeneoData['values']['label'] ?? null);

        return $productSeriesData;
    }
}
