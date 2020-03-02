<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\Model\Stock\ProductStockDataFactory;
use App\Model\Stock\ProductStockFacade;
use App\Model\Stock\StockFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductData as BaseProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactory as BaseProductDataFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;

class ProductDataFactory extends BaseProductDataFactory
{
    /**
     * @var \App\Model\Stock\ProductStockFacade
     */
    private $stockProductFacade;

    /**
     * @var \App\Model\Stock\StockFacade
     */
    private $stockFacade;

    /**
     * @var \App\Model\Stock\ProductStockDataFactory
     */
    private $stockProductDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade $productInputPriceFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginDataFormExtensionFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactoryInterface $productParameterValueDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade $pricingGroupFacade
     * @param \App\Model\Stock\ProductStockFacade $stockProductFacade
     * @param \App\Model\Stock\StockFacade $stockFacade
     * @param \App\Model\Stock\ProductStockDataFactory $stockProductDataFactory
     */
    public function __construct(
        VatFacade $vatFacade,
        ProductInputPriceFacade $productInputPriceFacade,
        UnitFacade $unitFacade,
        Domain $domain,
        ProductRepository $productRepository,
        ParameterRepository $parameterRepository,
        FriendlyUrlFacade $friendlyUrlFacade,
        ProductAccessoryRepository $productAccessoryRepository,
        ImageFacade $imageFacade,
        PluginCrudExtensionFacade $pluginDataFormExtensionFacade,
        ProductParameterValueDataFactoryInterface $productParameterValueDataFactory,
        PricingGroupFacade $pricingGroupFacade,
        ProductStockFacade $stockProductFacade,
        StockFacade $stockFacade,
        ProductStockDataFactory $stockProductDataFactory
    ) {
        parent::__construct($vatFacade, $productInputPriceFacade, $unitFacade, $domain, $productRepository, $parameterRepository, $friendlyUrlFacade, $productAccessoryRepository, $imageFacade, $pluginDataFormExtensionFacade, $productParameterValueDataFactory, $pricingGroupFacade);
        $this->stockProductFacade = $stockProductFacade;
        $this->stockFacade = $stockFacade;
        $this->stockProductDataFactory = $stockProductDataFactory;
    }

    /**
     * @return \App\Model\Product\ProductData
     */
    public function create(): BaseProductData
    {
        $productData = new ProductData();
        $this->fillNew($productData);
        $this->fillStockProductByStocks($productData);
        return $productData;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\ProductData
     */
    public function createFromProduct(BaseProduct $product): BaseProductData
    {
        $productData = new ProductData();
        $this->fillFromProduct($productData, $product);
        $this->fillStockProductByProduct($productData, $product);
        return $productData;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    protected function fillNew(BaseProductData $productData)
    {
        parent::fillNew($productData);

        foreach ($this->domain->getAllIds() as $domainId) {
            $productData->shortDescriptionUsp1[$domainId] = null;
            $productData->shortDescriptionUsp2[$domainId] = null;
            $productData->shortDescriptionUsp3[$domainId] = null;
            $productData->shortDescriptionUsp4[$domainId] = null;
            $productData->shortDescriptionUsp5[$domainId] = null;
            $productData->lowPriceWithVat[$domainId] = null;
            $productData->highPriceWithVat[$domainId] = null;
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $productData->namePrefix[$locale] = null;
            $productData->nameSufix[$locale] = null;
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product $product
     */
    protected function fillFromProduct(BaseProductData $productData, BaseProduct $product)
    {
        parent::fillFromProduct($productData, $product);

        foreach ($this->domain->getAllIds() as $domainId) {
            $productData->shortDescriptionUsp1[$domainId] = $product->getShortDescriptionUsp1($domainId);
            $productData->shortDescriptionUsp2[$domainId] = $product->getShortDescriptionUsp2($domainId);
            $productData->shortDescriptionUsp3[$domainId] = $product->getShortDescriptionUsp3($domainId);
            $productData->shortDescriptionUsp4[$domainId] = $product->getShortDescriptionUsp4($domainId);
            $productData->shortDescriptionUsp5[$domainId] = $product->getShortDescriptionUsp5($domainId);
            $productData->lowPriceWithVat[$domainId] = $product->getLowPriceWithVat($domainId);
            $productData->highPriceWithVat[$domainId] = $product->getHighPriceWithVat($domainId);
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $productData->namePrefix[$locale] = $product->getNamePrefix($locale);
            $productData->nameSufix[$locale] = $product->getNameSufix($locale);
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    protected function fillStockProductByStocks(BaseProductData $productData)
    {
        foreach ($this->stockFacade->getAllStocks() as $stock) {
            $productData->stockProductData[$stock->getId()] = $this->stockProductDataFactory->createFromStock($stock);
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product $product
     */
    protected function fillStockProductByProduct(BaseProductData $productData, BaseProduct $product)
    {
        $this->fillStockProductByStocks($productData);
        foreach ($this->stockProductFacade->getProductStocksByProduct($product) as $stockProduct) {
            $productData->stockProductData[$stockProduct->getStock()->getId()] = $this->stockProductDataFactory->createFromProductStock($stockProduct);
        }
    }
}
