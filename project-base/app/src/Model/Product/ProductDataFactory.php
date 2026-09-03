<?php

declare(strict_types=1);

namespace App\Model\Product;

use Override;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductData as BaseProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactory as BaseProductDataFactory;

/**
 * @method \App\Model\Product\Product[] getAccessoriesData(\App\Model\Product\Product $product)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData[] getParametersData(\App\Model\Product\Product $product)
 * @property \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade
 * @property \App\Model\Product\Parameter\ParameterRepository $parameterRepository
 * @method void fillProductStockByProduct(\App\Model\Product\ProductData $productData, \App\Model\Product\Product $product)
 * @method void fillProductStockByStocks(\App\Model\Product\ProductData $productData)
 * @method void fillNew(\App\Model\Product\ProductData $productData)
 * @method __construct(\Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\Model\Product\Parameter\ParameterRepository $parameterRepository, \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade, \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository, \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginDataFormExtensionFacade, \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactory $productParameterValueDataFactory, \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory, \Shopsys\FrameworkBundle\Model\Stock\ProductStockFacade $productStockFacade, \Shopsys\FrameworkBundle\Model\Stock\StockFacade $stockFacade, \Shopsys\FrameworkBundle\Model\Stock\ProductStockDataFactory $productStockDataFactory, \Shopsys\FrameworkBundle\Model\Product\ProductInputPriceDataFactory $productInputPriceDataFactory, \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory $uploadedFileDataFactory, \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoDataFactory $productVideoDataFactory, \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoRepository $productVideoRepository, \Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyDataFactory $productPromotionXyDataFactory, \Shopsys\FrameworkBundle\Model\Seo\SeoAttributesDataFactory $seoAttributesDataFactory)
 * @method void fillProductVideosByProductId(\App\Model\Product\ProductData $productData, \App\Model\Product\Product $product)
 */
class ProductDataFactory extends BaseProductDataFactory
{
    /**
     * @return \App\Model\Product\ProductData
     */
    #[Override]
    protected function createInstance(): BaseProductData
    {
        return new ProductData();
    }

    /**
     * @return \App\Model\Product\ProductData
     */
    #[Override]
    public function create(): BaseProductData
    {
        /** @var \App\Model\Product\ProductData $productData */
        $productData = parent::create();

        $this->fillProductStockByStocks($productData);

        return $productData;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\ProductData
     */
    #[Override]
    public function createFromProduct(BaseProduct $product): BaseProductData
    {
        /** @var \App\Model\Product\ProductData $productData */
        $productData = parent::createFromProduct($product);

        $this->fillProductStockByProduct($productData, $product);

        return $productData;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product $product
     */
    #[Override]
    protected function fillFromProduct(BaseProductData $productData, BaseProduct $product): void
    {
        parent::fillFromProduct($productData, $product);

        $productData->files = $this->uploadedFileDataFactory->createByEntity($product);
    }
}
