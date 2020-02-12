<?php

declare(strict_types=1);

namespace App\Model\Product\Series;

interface ProductSeriesFactoryInterface
{
    /**
     * @param \App\Model\Product\Series\ProductSeriesData $productSeriesData
     * @return \App\Model\Product\Series\ProductSeries
     */
    public function create(ProductSeriesData $productSeriesData): ProductSeries;
}
