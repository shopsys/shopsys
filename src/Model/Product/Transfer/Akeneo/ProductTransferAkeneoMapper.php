<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\Product\AkeneoProductHelper;
use App\Model\Category\CategoryFacade;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFilesData;
use App\Model\Product\ProductFilesDataFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class ProductTransferAkeneoMapper
{
    /**
     * @var \App\Model\Product\ProductDataFactory
     */
    private $productDataFactory;

    /**
     * @var \App\Model\Product\ProductFilesDataFactory
     */
    private $productFilesDataFactory;

    /**
     * @var \App\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @param \App\Model\Product\ProductDataFactory $productDataFactory
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\Product\ProductFilesDataFactory $productFilesDataFactory
     */
    public function __construct(
        ProductDataFactory $productDataFactory,
        CategoryFacade $categoryFacade,
        ProductFilesDataFactory $productFilesDataFactory
    ) {
        $this->productDataFactory = $productDataFactory;
        $this->categoryFacade = $categoryFacade;
        $this->productFilesDataFactory = $productFilesDataFactory;
    }

    /**
     * @param array $akeneoProductData
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\ProductFilesData
     */
    public function mapAkeneoProductDataToProductFilesData(array $akeneoProductData, Product $product): ProductFilesData
    {
        $productFilesData = $this->productFilesDataFactory->createFromProduct($product);

        $productFilesData->assemblyInstructionCode = AkeneoProductHelper::mapDomainDataStringWithoutClean(
            $productFilesData->assemblyInstructionCode,
            $akeneoProductData['values']['assembly_instruction'] ?? null
        );

        $productFilesData->productTypePlanCode = AkeneoProductHelper::mapDomainDataStringWithoutClean(
            $productFilesData->productTypePlanCode,
            $akeneoProductData['values']['product_type_plan'] ?? null
        );

        return $productFilesData;
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

        $productData->lowPriceWithVat = AkeneoProductHelper::mapDomainDataPrices($productData->lowPriceWithVat, $akeneoProductData['values']['low_price_vat'] ?? null);
        $productData->highPriceWithVat = AkeneoProductHelper::mapDomainDataPrices($productData->highPriceWithVat, $akeneoProductData['values']['high_price_vat'] ?? null);

        $productCategories = $this->getProductCategories($akeneoProductData['categories']);
        $productData->categoriesByDomainId = [
            Domain::FIRST_DOMAIN_ID => $productCategories,
            Domain::SECOND_DOMAIN_ID => $productCategories,
        ];

        return $productData;
    }

    /**
     * @param string[] $akeneoCategoryCodes
     * @return \App\Model\Category\Category[]
     */
    protected function getProductCategories(array $akeneoCategoryCodes): array
    {
        $productCategories = [];

        foreach ($akeneoCategoryCodes as $categoryAkeneoCode) {
            $category = $this->categoryFacade->findByAkeneoCode($categoryAkeneoCode);

            if ($category === null) {
                continue;
            }

            $productCategories[$category->getId()] = $category;

            foreach ($category->getParentsWithoutRootCategory() as $parentCategory) {
                $productCategories[$parentCategory->getId()] = $parentCategory;
            }
        }

        return $productCategories;
    }
}
