<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\Product\AkeneoProductHelper;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use App\Model\Product\ProductDataFactory;

class ProductTransferAkeneoMapper
{
    /**
     * @var \App\Model\Product\ProductDataFactory
     */
    private $productDataFactory;

    /**
     * @param \App\Model\Product\ProductDataFactory $productDataFactory
     */
    public function __construct(
        ProductDataFactory $productDataFactory
    ) {
        $this->productDataFactory = $productDataFactory;
    }

    /**
     * @param array $akeneoProductData
     * @param \App\Model\Product\Product|null $product
     * @return \App\Model\Product\ProductData
     */
    public function mapAkeneoProductDataToProductData(array $akeneoProductData, ?Product $product): ProductData
    {
        if ($product === null) {
            $productData = $this->productDataFactory->create();
            $productData->catnum = $akeneoProductData['identifier'];
        } else {
            $productData = $this->productDataFactory->createFromProduct($product);
        }

        $productData->hidden = $akeneoProductData['enabled'] ? false : true;

        $productData->ean = AkeneoProductHelper::mapDataString($akeneoProductData['values']['ean'] ?? null);

        $productData->namePrefix = AkeneoProductHelper::mapLocalizedDataString($productData->namePrefix, $akeneoProductData['values']['name_prefix'] ?? null);
        $productData->name = AkeneoProductHelper::mapLocalizedDataString($productData->name, $akeneoProductData['values']['name'] ?? null);
        $productData->nameSufix = AkeneoProductHelper::mapLocalizedDataString($productData->nameSufix, $akeneoProductData['values']['name_sufix'] ?? null);

        $productData->descriptions = AkeneoProductHelper::mapDomainDataString($productData->descriptions, $akeneoProductData['values']['description'] ?? null);
        $productData->shortDescriptionUsp1 = AkeneoProductHelper::mapDomainDataString($productData->shortDescriptionUsp1, $akeneoProductData['values']['usp1'] ?? null);
        $productData->shortDescriptionUsp2 = AkeneoProductHelper::mapDomainDataString($productData->shortDescriptionUsp2, $akeneoProductData['values']['usp2'] ?? null);
        $productData->shortDescriptionUsp3 = AkeneoProductHelper::mapDomainDataString($productData->shortDescriptionUsp3, $akeneoProductData['values']['usp3'] ?? null);
        $productData->shortDescriptionUsp4 = AkeneoProductHelper::mapDomainDataString($productData->shortDescriptionUsp4, $akeneoProductData['values']['usp4'] ?? null);
        $productData->shortDescriptionUsp5 = AkeneoProductHelper::mapDomainDataString($productData->shortDescriptionUsp5, $akeneoProductData['values']['usp5'] ?? null);

        return $productData;
    }
}
