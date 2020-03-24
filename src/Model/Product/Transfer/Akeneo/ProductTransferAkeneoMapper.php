<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\Product\AkeneoProductHelper;
use App\Component\Akeneo\Transfer\Exception\TransferInvalidDataException;
use App\Model\Category\CategoryFacade;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFilesData;
use App\Model\Product\ProductFilesDataFactory;
use App\Model\Product\Type\ProductTypeFacade;
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
     * @var \App\Model\Product\Type\ProductTypeFacade
     */
    private $productTypeFacade;

    /**
     * @param \App\Model\Product\ProductDataFactory $productDataFactory
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\Product\ProductFilesDataFactory $productFilesDataFactory
     * @param \App\Model\Product\Type\ProductTypeFacade $productTypeFacade
     */
    public function __construct(
        ProductDataFactory $productDataFactory,
        CategoryFacade $categoryFacade,
        ProductFilesDataFactory $productFilesDataFactory,
        ProductTypeFacade $productTypeFacade
    ) {
        $this->productDataFactory = $productDataFactory;
        $this->categoryFacade = $categoryFacade;
        $this->productFilesDataFactory = $productFilesDataFactory;
        $this->productTypeFacade = $productTypeFacade;
    }

    /**
     * @param array $akeneoProductData
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\ProductFilesData
     */
    public function mapAkeneoProductDataToProductFilesData(array $akeneoProductData, Product $product): ProductFilesData
    {
        $productFilesData = $this->productFilesDataFactory->createFromProduct($product);

        $productFilesData->assemblyInstructionCode = AkeneoProductHelper::mapDomainDataString(
            $productFilesData->assemblyInstructionCode,
            $akeneoProductData['values']['assembly_instruction'] ?? null
        );

        $productFilesData->productTypePlanCode = AkeneoProductHelper::mapDomainDataString(
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
        $productData->productType = $this->getProductType($productData->productType, $akeneoProductData);

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

    /**
     * @param \App\Model\Product\Type\ProductType[]|null[] $productTypesByDomainId
     * @param array $akeneoProductData
     * @return \App\Model\Product\Type\ProductType[]
     */
    private function getProductType(array $productTypesByDomainId, array $akeneoProductData): array
    {
        $productTypeAkeneoCodesByDomainId = [];
        foreach ($productTypesByDomainId as $domainId => $productType) {
            if ($productType === null) {
                $productTypeAkeneoCodesByDomainId[$domainId] = null;
            } else {
                $productTypeAkeneoCodesByDomainId[$domainId] = $productType->getAkeneoCode();
            }
        }

        $akeneoCodesByDomainId = AkeneoProductHelper::mapDomainDataString(
            $productTypeAkeneoCodesByDomainId,
            $akeneoProductData['values']['product_type']
        );
        foreach ($akeneoCodesByDomainId as $domainId => $akeneoCode) {
            if ($akeneoCode === null) {
                throw TransferInvalidDataException::createWithViolation(
                    sprintf('ProductType for domain `%s` is required', $domainId),
                    'product_type'
                );
            }

            $productType = $this->productTypeFacade->findByAkeneoCode($akeneoCode);
            if ($productType === null) {
                throw TransferInvalidDataException::createWithViolation(
                    sprintf('ProductType with Akeneo code `%s` wasn\'t found.', $akeneoCode),
                    'product_type'
                );
            }

            $productTypesByDomainId[$domainId] = $productType;
        }

        return $productTypesByDomainId;
    }
}
