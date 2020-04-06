<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\Component\Setting\Setting;
use App\Model\Stock\ProductStockDataFactory;
use App\Model\Stock\ProductStockFacade;
use App\Model\Stock\StockFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade;
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
     * @var \Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation
     */
    private $basePriceCalculation;

    /**
     * @var \App\Model\Product\ProductFacade
     */
    private $productFacade;

    /**
     * @var \App\Component\Setting\Setting
     */
    private $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade
     */
    private $availabilityFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade $productInputPriceFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Product\ProductRepository $productRepository
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
     * @param \Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation $basePriceCalculation
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \App\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade $availabilityFacade
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
        ProductStockDataFactory $stockProductDataFactory,
        BasePriceCalculation $basePriceCalculation,
        ProductFacade $productFacade,
        Setting $setting,
        AvailabilityFacade $availabilityFacade
    ) {
        parent::__construct(
            $vatFacade,
            $productInputPriceFacade,
            $unitFacade,
            $domain,
            $productRepository,
            $parameterRepository,
            $friendlyUrlFacade,
            $productAccessoryRepository,
            $imageFacade,
            $pluginDataFormExtensionFacade,
            $productParameterValueDataFactory,
            $pricingGroupFacade
        );
        $this->stockProductFacade = $stockProductFacade;
        $this->stockFacade = $stockFacade;
        $this->stockProductDataFactory = $stockProductDataFactory;
        $this->basePriceCalculation = $basePriceCalculation;
        $this->productFacade = $productFacade;
        $this->setting = $setting;
        $this->availabilityFacade = $availabilityFacade;
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
        $this->fillProductFilesAttributesFromProduct($productData, $product);

        return $productData;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    protected function fillNew(BaseProductData $productData): void
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
            $productData->lowPriceWithoutVat[$domainId] = null;
            $productData->highPriceWithoutVat[$domainId] = null;
            $productData->assemblyInstructionFileUrl[$domainId] = null;
            $productData->productTypePlanFileUrl[$domainId] = null;
            $productData->productType[$domainId] = null;
            $productData->preorder = false;
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $productData->namePrefix[$locale] = null;
            $productData->nameSufix[$locale] = null;
        }

        $productData->availability = $this->availabilityFacade->getById($this->setting->get('defaultAvailabilityInStockId'));
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product $product
     */
    protected function fillFromProduct(BaseProductData $productData, BaseProduct $product): void
    {
        parent::fillFromProduct($productData, $product);

        $productData->downloadAssemblyInstructionFiles = $product->isDownloadAssemblyInstructionFiles();
        $productData->downloadProductTypePlanFiles = $product->isDownloadAssemblyInstructionFiles();

        foreach ($this->domain->getAllIds() as $domainId) {
            $productData->shortDescriptionUsp1[$domainId] = $product->getShortDescriptionUsp1($domainId);
            $productData->shortDescriptionUsp2[$domainId] = $product->getShortDescriptionUsp2($domainId);
            $productData->shortDescriptionUsp3[$domainId] = $product->getShortDescriptionUsp3($domainId);
            $productData->shortDescriptionUsp4[$domainId] = $product->getShortDescriptionUsp4($domainId);
            $productData->shortDescriptionUsp5[$domainId] = $product->getShortDescriptionUsp5($domainId);
            $productData->productType[$domainId] = $product->getProductType($domainId);

            $this->fillPricesFromProductByDomain($productData, $product, $domainId);
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $productData->namePrefix[$locale] = $product->getNamePrefix($locale);
            $productData->nameSufix[$locale] = $product->getNameSufix($locale);
        }

        $productData->preorder = $product->hasPreorder();
        $productData->vendorDeliveryDate = $product->getVendorDeliveryDate();
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     */
    private function fillPricesFromProductByDomain(ProductData $productData, Product $product, int $domainId): void
    {
        //TODO-RK ne deprecated zpusob prace s price ale nefunguje spravne zaokrouhleni podle meny,
        $lowPrice = new Price(Money::zero(), $product->getLowPriceWithVat($domainId) ?? Money::zero());
        $highPrice = new Price(Money::zero(), $product->getHighPriceWithVat($domainId) ?? Money::zero());

        $lowPrice = $this->basePriceCalculation->applyCoefficients($lowPrice, $product->getVatForDomain($domainId), []);
        $highPrice = $this->basePriceCalculation->applyCoefficients($highPrice, $product->getVatForDomain($domainId), []);

        //TODO-RK deprecate zpusob prace s price
//        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId());
//        $lowPrice = $this->basePriceCalculation->calculateBasePriceRoundedByCurrency(
//            $product->getLowPriceWithVat($domainId) ?? Money::zero(),
//            PricingSetting::INPUT_PRICE_TYPE_WITH_VAT,
//            $product->getVatForDomain($domainId),
//            $currency
//        );
//
//        $highPrice = $this->basePriceCalculation->calculateBasePriceRoundedByCurrency(
//            $product->getHighPriceWithVat($domainId) ?? Money::zero(),
//            PricingSetting::INPUT_PRICE_TYPE_WITH_VAT,
//            $product->getVatForDomain($domainId),
//            $currency
//        );

        $productData->lowPriceWithVat[$domainId] = $lowPrice->getPriceWithVat();
        $productData->highPriceWithVat[$domainId] = $highPrice->getPriceWithVat();

        $productData->lowPriceWithoutVat[$domainId] = $lowPrice->getPriceWithoutVat();
        $productData->highPriceWithoutVat[$domainId] = $highPrice->getPriceWithoutVat();
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product $product
     */
    private function fillProductFilesAttributesFromProduct(ProductData $productData, Product $product): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $productData->assemblyInstructionFileUrl[$domainId] = null;
            $productData->productTypePlanFileUrl[$domainId] = null;

            if ($product->getAssemblyInstructionCode($domainId) !== null) {
                $productData->assemblyInstructionFileUrl[$domainId] = $this->productFacade->getProductTransferredFileUrl(
                    $product->getProductFileNameByType($domainId, Product::FILE_IDENTIFICATOR_ASSEMBLY_INSTRUCTION_TYPE),
                    $domainConfig->getUrl()
                );
            }

            if ($product->getProductTypePlanCode($domainId) !== null) {
                $productData->productTypePlanFileUrl[$domainId] = $this->productFacade->getProductTransferredFileUrl(
                    $product->getProductFileNameByType($domainId, Product::FILE_IDENTIFICATOR_PRODUCT_TYPE_PLAN_TYPE),
                    $domainConfig->getUrl()
                );
            }
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    private function fillStockProductByStocks(BaseProductData $productData): void
    {
        foreach ($this->stockFacade->getAllStocks() as $stock) {
            $productData->stockProductData[$stock->getId()] = $this->stockProductDataFactory->createFromStock($stock);
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product $product
     */
    private function fillStockProductByProduct(BaseProductData $productData, BaseProduct $product): void
    {
        $this->fillStockProductByStocks($productData);
        foreach ($this->stockProductFacade->getProductStocksByProduct($product) as $stockProduct) {
            $productData->stockProductData[$stockProduct->getStock()->getId()] = $this->stockProductDataFactory->createFromProductStock($stockProduct);
        }
    }
}
