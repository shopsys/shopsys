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
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    private $currencyFacade;

    /**
     * @return \App\Model\Product\ProductData
     */
    protected function createInstance(): BaseProductData
    {
        return new ProductData();
    }

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
     * @var \App\Model\Product\Availability\AvailabilityFacade
     */
    protected $availabilityFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade $productInputPriceFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginDataFormExtensionFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactoryInterface $productParameterValueDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade $pricingGroupFacade
     * @param \App\Model\Stock\ProductStockFacade $stockProductFacade
     * @param \App\Model\Stock\StockFacade $stockFacade
     * @param \App\Model\Stock\ProductStockDataFactory $stockProductDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation $basePriceCalculation
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \App\Component\Setting\Setting $setting
     * @param \App\Model\Product\Availability\AvailabilityFacade $availabilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
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
        AvailabilityFacade $availabilityFacade,
        CurrencyFacade $currencyFacade
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
        $this->currencyFacade = $currencyFacade;
    }

    /**
     * @return \App\Model\Product\ProductData
     */
    public function create(): BaseProductData
    {
        $productData = $this->createInstance();
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
        $productData = $this->createInstance();
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
            $productData->flags[$domainId] = [];
            $productData->saleExclusion[$domainId] = false;
            $productData->mountingState[$domainId] = null;
            $productData->countPackages[$domainId] = null;
            $productData->packageNotIncluded[$domainId] = null;
            $productData->packagingUnit[$domainId] = null;
            $productData->totalPackageWeight[$domainId] = null;
            $productData->embeddedAccessories[$domainId] = null;
            $productData->domainHidden[$domainId] = false;
            $productData->domainOrderingPriority[$domainId] = 0;
            $productData->canBeShippedAsPackage[$domainId] = false;
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $productData->namePrefix[$locale] = null;
            $productData->nameSufix[$locale] = null;
        }

        $productData->preorder = false;
        $productData->availability = $this->availabilityFacade->getById($this->setting->get('defaultAvailabilityInStockId'));
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product $product
     */
    protected function fillFromProduct(BaseProductData $productData, BaseProduct $product): void
    {
        /** @var \App\Model\Product\ProductTranslation[] $translations */
        $translations = $product->getTranslations();
        foreach ($translations as $translation) {
            $locale = $translation->getLocale();

            $productData->name[$locale] = $translation->getName();
            $productData->variantAlias[$locale] = $translation->getVariantAlias();
            $productData->namePrefix[$locale] = $translation->getNamePrefix();
            $productData->nameSufix[$locale] = $translation->getNameSufix();
        }

        foreach ($this->domain->getAllIds() as $domainId) {
            $productData->shortDescriptions[$domainId] = $product->getShortDescription($domainId);
            $productData->descriptions[$domainId] = $product->getDescription($domainId);
            $productData->seoH1s[$domainId] = $product->getSeoH1($domainId);
            $productData->seoTitles[$domainId] = $product->getSeoTitle($domainId);
            $productData->seoMetaDescriptions[$domainId] = $product->getSeoMetaDescription($domainId);
            $productData->vatsIndexedByDomainId[$domainId] = $product->getVatForDomain($domainId);

            $productData->shortDescriptionUsp1[$domainId] = $product->getShortDescriptionUsp1($domainId);
            $productData->shortDescriptionUsp2[$domainId] = $product->getShortDescriptionUsp2($domainId);
            $productData->shortDescriptionUsp3[$domainId] = $product->getShortDescriptionUsp3($domainId);
            $productData->shortDescriptionUsp4[$domainId] = $product->getShortDescriptionUsp4($domainId);
            $productData->shortDescriptionUsp5[$domainId] = $product->getShortDescriptionUsp5($domainId);
            $productData->flags[$domainId] = $product->getFlagsForDomain($domainId);
            $productData->saleExclusion[$domainId] = $product->getSaleExclusion($domainId);
            $productData->mountingState[$domainId] = $product->isMountingState($domainId);
            $productData->countPackages[$domainId] = $product->getCountPackages($domainId);
            $productData->packageNotIncluded[$domainId] = $product->getPackageNotIncluded($domainId);
            $productData->packagingUnit[$domainId] = $product->getPackagingUnit($domainId);
            $productData->totalPackageWeight[$domainId] = $product->getTotalPackageWeight($domainId);
            $productData->embeddedAccessories[$domainId] = $product->getEmbeddedAccessories($domainId);
            $productData->domainHidden[$domainId] = $product->isDomainHidden($domainId);
            $productData->domainOrderingPriority[$domainId] = $product->getDomainOrderingPriority($domainId);
            $productData->canBeShippedAsPackage[$domainId] = $product->canBeShippedAsPackage($domainId);

            $this->fillPricesFromProductByDomain($productData, $product, $domainId);
        }

        $productData->catnum = $product->getCatnum();
        $productData->partno = $product->getPartno();
        $productData->ean = $product->getEan();
        $productData->sellingFrom = $product->getSellingFrom();
        $productData->sellingTo = $product->getSellingTo();
        $productData->sellingDenied = $product->isSellingDenied();

        $productData->availability = $this->availabilityFacade->getById($this->setting->get('defaultAvailabilityInStockId'));

        $productData->unit = $product->getUnit();

        $productData->hidden = $product->isHidden();
        $productData->categoriesByDomainId = $product->getCategoriesIndexedByDomainId();
        $productData->brand = $product->getBrand();
        $productData->orderingPriority = $product->getOrderingPriority();

        $productData->parameters = $this->getParametersData($product);
        try {
            $productData->manualInputPricesByPricingGroupId = $this->productInputPriceFacade->getManualInputPricesDataIndexedByPricingGroupId($product);
        } catch (\Shopsys\FrameworkBundle\Model\Product\Pricing\Exception\MainVariantPriceCalculationException $ex) {
            $productData->manualInputPricesByPricingGroupId = $this->getNullForAllPricingGroups();
        }

        /** @var \App\Model\Product\Product[] $productAccessories */
        $productAccessories = $this->getAccessoriesData($product);

        $productData->accessories = $productAccessories;
        $productData->images->orderedImages = $this->imageFacade->getImagesByEntityIndexedById($product, null);
        $productData->variants = $product->getVariants();
        $productData->pluginData = $this->pluginDataFormExtensionFacade->getAllData('product', $product->getId());

        $productData->downloadAssemblyInstructionFiles = $product->isDownloadAssemblyInstructionFiles();
        $productData->downloadProductTypePlanFiles = $product->isDownloadAssemblyInstructionFiles();

        $productData->preorder = $product->hasPreorder();
        $productData->vendorDeliveryDate = $product->getVendorDeliveryDate();
        $productData->variantParameters = $product->getVariantParameters();
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     */
    private function fillPricesFromProductByDomain(ProductData $productData, Product $product, int $domainId): void
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $lowPrice = $this->basePriceCalculation->calculateBasePriceRoundedByCurrency(
            $product->getLowPriceWithVat($domainId) ?? Money::zero(),
            PricingSetting::INPUT_PRICE_TYPE_WITH_VAT,
            $product->getVatForDomain($domainId),
            $currency
        );

        $highPrice = $this->basePriceCalculation->calculateBasePriceRoundedByCurrency(
            $product->getHighPriceWithVat($domainId) ?? Money::zero(),
            PricingSetting::INPUT_PRICE_TYPE_WITH_VAT,
            $product->getVatForDomain($domainId),
            $currency
        );

        $sellingPrice = $this->basePriceCalculation->calculateBasePriceRoundedByCurrency(
            $product->getSellingPriceWithVat($domainId) ?? Money::zero(),
            PricingSetting::INPUT_PRICE_TYPE_WITH_VAT,
            $product->getVatForDomain($domainId),
            $currency
        );

        $productData->lowPriceWithVat[$domainId] = $lowPrice->getPriceWithVat();
        $productData->highPriceWithVat[$domainId] = $highPrice->getPriceWithVat();

        $productData->lowPriceWithoutVat[$domainId] = $lowPrice->getPriceWithoutVat();
        $productData->highPriceWithoutVat[$domainId] = $highPrice->getPriceWithoutVat();

        $productData->sellingPriceWithVat[$domainId] = $sellingPrice->getPriceWithVat();
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

        ksort($productData->stockProductData);
    }
}
