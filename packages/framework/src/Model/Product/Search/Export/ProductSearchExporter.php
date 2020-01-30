<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search\Export;

use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchConverter;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;

class ProductSearchExporter
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportWithFilterRepository
     */
    protected $productSearchExportWithFilterRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository
     */
    protected $productElasticsearchRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchConverter
     */
    protected $productElasticsearchConverter;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportWithFilterRepository $productSearchExportWithFilterRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchConverter $productElasticsearchConverter
     */
    public function __construct(
        ProductSearchExportWithFilterRepository $productSearchExportWithFilterRepository,
        ProductElasticsearchRepository $productElasticsearchRepository,
        ProductElasticsearchConverter $productElasticsearchConverter
    ) {
        $this->productSearchExportWithFilterRepository = $productSearchExportWithFilterRepository;
        $this->productElasticsearchRepository = $productElasticsearchRepository;
        $this->productElasticsearchConverter = $productElasticsearchConverter;
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param int[] $productIds
     */
    public function exportIds(int $domainId, string $locale, array $productIds): void
    {
        $productsData = $this->productSearchExportWithFilterRepository->getProductsDataForIds($domainId, $locale, $productIds);
        if (count($productsData) === 0) {
            $this->productElasticsearchRepository->delete($domainId, $productIds);

            return;
        }

        $this->exportProductsData($domainId, $productsData);
        $exportedIds = $this->productElasticsearchConverter->extractIds($productsData);

        $idsToDelete = array_diff($productIds, $exportedIds);

        if ($idsToDelete !== []) {
            $this->productElasticsearchRepository->delete($domainId, $idsToDelete);
        }
    }

    /**
     * @param int $domainId
     * @param array $productsData
     */
    protected function exportProductsData(int $domainId, array $productsData): void
    {
        $data = $this->productElasticsearchConverter->convertExportBulk($productsData);
        $this->productElasticsearchRepository->bulkUpdate($domainId, $data);
    }
}
