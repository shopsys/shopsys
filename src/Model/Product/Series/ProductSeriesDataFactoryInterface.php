<?php

declare(strict_types=1);

namespace App\Model\Product\Series;

interface ProductSeriesDataFactoryInterface
{
    /**
     * @return \App\Model\Product\Series\ProductSeriesData
     */
    public function create(): ProductSeriesData;

    /**
     * @param \App\Model\Product\Series\ProductSeries $productSeries
     * @return \App\Model\Product\Series\ProductSeriesData
     */
    public function createFromProductSeries(ProductSeries $productSeries): ProductSeriesData;
}
