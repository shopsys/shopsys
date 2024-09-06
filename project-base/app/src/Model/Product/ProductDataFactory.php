<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\Model\ProductVideo\ProductVideoDataFactory;
use App\Model\ProductVideo\ProductVideoRepository;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductData as BaseProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactory as BaseProductDataFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductInputPriceDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;
use Shopsys\FrameworkBundle\Model\Stock\ProductStockDataFactory;
use Shopsys\FrameworkBundle\Model\Stock\ProductStockFacade;
use Shopsys\FrameworkBundle\Model\Stock\StockFacade;

/**
 * @method \App\Model\Product\Product[] getAccessoriesData(\App\Model\Product\Product $product)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData[] getParametersData(\App\Model\Product\Product $product)
 * @property \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade
 * @property \App\Model\Product\Parameter\ParameterRepository $parameterRepository
 * @method fillProductStockByProduct(\App\Model\Product\ProductData $productData, \App\Model\Product\Product $product)
 * @method fillProductStockByStocks(\App\Model\Product\ProductData $productData)
 */
class ProductDataFactory extends BaseProductDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginDataFormExtensionFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactoryInterface $productParameterValueDataFactory
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Stock\ProductStockFacade $productStockFacade
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockFacade $stockFacade
     * @param \Shopsys\FrameworkBundle\Model\Stock\ProductStockDataFactory $productStockDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductInputPriceDataFactory $productInputPriceDataFactory
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory $uploadedFileDataFactory
     * @param \App\Model\ProductVideo\ProductVideoDataFactory $productVideoDataFactory
     * @param \App\Model\ProductVideo\ProductVideoRepository $productVideoRepository
     */
    public function __construct(
        UnitFacade $unitFacade,
        Domain $domain,
        ParameterRepository $parameterRepository,
        FriendlyUrlFacade $friendlyUrlFacade,
        ProductAccessoryRepository $productAccessoryRepository,
        PluginCrudExtensionFacade $pluginDataFormExtensionFacade,
        ProductParameterValueDataFactoryInterface $productParameterValueDataFactory,
        ImageUploadDataFactory $imageUploadDataFactory,
        ProductStockFacade $productStockFacade,
        StockFacade $stockFacade,
        ProductStockDataFactory $productStockDataFactory,
        ProductInputPriceDataFactory $productInputPriceDataFactory,
        UploadedFileDataFactory $uploadedFileDataFactory,
        private readonly ProductVideoDataFactory $productVideoDataFactory,
        private readonly ProductVideoRepository $productVideoRepository,
    ) {
        parent::__construct(
            $unitFacade,
            $domain,
            $parameterRepository,
            $friendlyUrlFacade,
            $productAccessoryRepository,
            $pluginDataFormExtensionFacade,
            $productParameterValueDataFactory,
            $imageUploadDataFactory,
            $productStockFacade,
            $stockFacade,
            $productStockDataFactory,
            $productInputPriceDataFactory,
            $uploadedFileDataFactory,
        );
    }

    /**
     * @return \App\Model\Product\ProductData
     */
    protected function createInstance(): BaseProductData
    {
        $productData = new ProductData();
        $productData->images = $this->imageUploadDataFactory->create();
        $productData->files = $this->uploadedFileDataFactory->create();

        return $productData;
    }

    /**
     * @return \App\Model\Product\ProductData
     */
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
    public function createFromProduct(BaseProduct $product): BaseProductData
    {
        /** @var \App\Model\Product\ProductData $productData */
        $productData = parent::createFromProduct($product);

        $this->fillProductStockByProduct($productData, $product);
        $this->fillProductVideosByProductId($productData, $product);

        return $productData;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    protected function fillNew(BaseProductData $productData): void
    {
        parent::fillNew($productData);

        foreach ($this->domain->getAllLocales() as $locale) {
            $productData->namePrefix[$locale] = null;
            $productData->nameSufix[$locale] = null;
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product $product
     */
    protected function fillFromProduct(BaseProductData $productData, BaseProduct $product): void
    {
        parent::fillFromProduct($productData, $product);

        /** @var \App\Model\Product\ProductTranslation[] $translations */
        $translations = $product->getTranslations();

        foreach ($translations as $translation) {
            $locale = $translation->getLocale();

            $productData->namePrefix[$locale] = $translation->getNamePrefix();
            $productData->nameSufix[$locale] = $translation->getNameSufix();
        }

        $productData->files = $this->uploadedFileDataFactory->createByEntity($product);
        $productData->relatedProducts = $product->getRelatedProducts();
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product $product
     */
    private function fillProductVideosByProductId(ProductData $productData, Product $product): void
    {
        foreach ($this->productVideoRepository->findByProductId($product->getId()) as $video) {
            $productData->productVideosData[$video->getid()] = $this->productVideoDataFactory->createFromProductVideo($video);
        }
    }
}
