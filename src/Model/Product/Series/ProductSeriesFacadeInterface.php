<?php

declare(strict_types=1);

namespace App\Model\Product\Series;

interface ProductSeriesFacadeInterface
{
    /**
     * @param \App\Model\Product\Series\ProductSeriesData $productSeriesData
     * @return \App\Model\Product\Series\ProductSeries
     */
    public function create(ProductSeriesData $productSeriesData): ProductSeries;

    /**
     * @param int $id
     * @param \App\Model\Product\Series\ProductSeriesData $productSeriesData
     * @return \App\Model\Product\Series\ProductSeries
     */
    public function edit(int $id, ProductSeriesData $productSeriesData): ProductSeries;

    /**
     * @param \App\Model\Product\Series\ProductSeries $productSeries
     */
    public function delete(ProductSeries $productSeries): void;

    /**
     * @param int $id
     * @return \App\Model\Product\Series\ProductSeries
     */
    public function getById(int $id): ProductSeries;

    /**
     * @param int $id
     * @param int $domainId
     * @return \App\Model\Product\Series\ProductSeries
     */
    public function getVisibleProductSeriesByIdAndDomainId(int $id, int $domainId): ProductSeries;

    /**
     * @param int $domainId
     * @return \App\Model\Product\Series\ProductSeries[]
     */
    public function getAllVisibleProductSeriesByDomainId(int $domainId): array;
}
