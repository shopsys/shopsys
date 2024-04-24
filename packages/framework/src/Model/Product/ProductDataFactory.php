<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Pricing\Exception\MainVariantPriceCalculationException;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;
use Shopsys\FrameworkBundle\Model\Stock\ProductStockDataFactory;
use Shopsys\FrameworkBundle\Model\Stock\ProductStockFacade;
use Shopsys\FrameworkBundle\Model\Stock\StockFacade;

class ProductDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade $productInputPriceFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginDataFormExtensionFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactoryInterface $productParameterValueDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade $pricingGroupFacade
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Stock\ProductStockFacade $productStockFacade
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockFacade $stockFacade
     * @param \Shopsys\FrameworkBundle\Model\Stock\ProductStockDataFactory $productStockDataFactory
     */
    public function __construct(
        protected readonly VatFacade $vatFacade,
        protected readonly ProductInputPriceFacade $productInputPriceFacade,
        protected readonly UnitFacade $unitFacade,
        protected readonly Domain $domain,
        protected readonly ParameterRepository $parameterRepository,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ProductAccessoryRepository $productAccessoryRepository,
        protected readonly PluginCrudExtensionFacade $pluginDataFormExtensionFacade,
        protected readonly ProductParameterValueDataFactoryInterface $productParameterValueDataFactory,
        protected readonly PricingGroupFacade $pricingGroupFacade,
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
        protected readonly ProductStockFacade $productStockFacade,
        protected readonly StockFacade $stockFacade,
        protected readonly ProductStockDataFactory $productStockDataFactory,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductData
     */
    protected function createInstance(): ProductData
    {
        $productData = new ProductData();
        $productData->images = $this->imageUploadDataFactory->create();

        return $productData;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductData
     */
    public function create(): ProductData
    {
        $productData = $this->createInstance();
        $this->fillNew($productData);

        return $productData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $productData
     */
    protected function fillNew(ProductData $productData): void
    {
        $productVatsIndexedByDomain = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $productVatsIndexedByDomain[$domainId] = $this->vatFacade->getDefaultVatForDomain($domainId);
            $productData->shortDescriptionUsp1ByDomainId[$domainId] = null;
            $productData->shortDescriptionUsp2ByDomainId[$domainId] = null;
            $productData->shortDescriptionUsp3ByDomainId[$domainId] = null;
            $productData->shortDescriptionUsp4ByDomainId[$domainId] = null;
            $productData->shortDescriptionUsp5ByDomainId[$domainId] = null;
            $productData->flagsByDomainId[$domainId] = [];
            $productData->orderingPriorityByDomainId[$domainId] = 0;
            $productData->saleExclusion[$domainId] = false;
            $productData->domainHidden[$domainId] = false;
        }

        $productData->vatsIndexedByDomainId = $productVatsIndexedByDomain;
        $productData->unit = $this->unitFacade->getDefaultUnit();

        $productParameterValuesData = [];
        $productData->parameters = $productParameterValuesData;

        $nullForAllDomains = $this->getNullForAllDomains();

        $productData->manualInputPricesByPricingGroupId = $this->getNullForAllPricingGroups();
        $productData->seoTitles = $nullForAllDomains;
        $productData->seoH1s = $nullForAllDomains;
        $productData->seoMetaDescriptions = $nullForAllDomains;
        $productData->descriptions = $nullForAllDomains;
        $productData->shortDescriptions = $nullForAllDomains;
        $productData->accessories = [];

        foreach ($this->domain->getAllLocales() as $locale) {
            $productData->name[$locale] = null;
            $productData->variantAlias[$locale] = null;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductData
     */
    public function createFromProduct(Product $product): ProductData
    {
        $productData = $this->createInstance();
        $this->fillFromProduct($productData, $product);

        return $productData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $productData
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     */
    protected function fillFromProduct(ProductData $productData, Product $product): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductTranslation[] $translations */
        $translations = $product->getTranslations();
        $names = [];
        $variantAliases = [];

        foreach ($translations as $translation) {
            $names[$translation->getLocale()] = $translation->getName();
            $variantAliases[$translation->getLocale()] = $translation->getVariantAlias();
        }

        foreach ($this->domain->getAllIds() as $domainId) {
            $productData->shortDescriptions[$domainId] = $product->getShortDescription($domainId);
            $productData->descriptions[$domainId] = $product->getDescription($domainId);
            $productData->seoH1s[$domainId] = $product->getSeoH1($domainId);
            $productData->seoTitles[$domainId] = $product->getSeoTitle($domainId);
            $productData->seoMetaDescriptions[$domainId] = $product->getSeoMetaDescription($domainId);
            $productData->vatsIndexedByDomainId[$domainId] = $product->getVatForDomain($domainId);
            $productData->shortDescriptionUsp1ByDomainId[$domainId] = $product->getShortDescriptionUsp1($domainId);
            $productData->shortDescriptionUsp2ByDomainId[$domainId] = $product->getShortDescriptionUsp2($domainId);
            $productData->shortDescriptionUsp3ByDomainId[$domainId] = $product->getShortDescriptionUsp3($domainId);
            $productData->shortDescriptionUsp4ByDomainId[$domainId] = $product->getShortDescriptionUsp4($domainId);
            $productData->shortDescriptionUsp5ByDomainId[$domainId] = $product->getShortDescriptionUsp5($domainId);
            $productData->flagsByDomainId[$domainId] = $product->getFlags($domainId);
            $productData->orderingPriorityByDomainId[$domainId] = $product->getOrderingPriority($domainId);
            $productData->saleExclusion[$domainId] = $product->getSaleExclusion($domainId);
            $productData->domainHidden[$domainId] = $product->isDomainHidden($domainId);

            $mainFriendlyUrl = $this->friendlyUrlFacade->findMainFriendlyUrl(
                $domainId,
                'front_product_detail',
                $product->getId(),
            );
            $productData->urls->mainFriendlyUrlsByDomainId[$domainId] = $mainFriendlyUrl;
        }
        $productData->name = $names;
        $productData->variantAlias = $variantAliases;

        $productData->catnum = $product->getCatnum();
        $productData->partno = $product->getPartno();
        $productData->ean = $product->getEan();
        $productData->sellingFrom = $product->getSellingFrom();
        $productData->sellingTo = $product->getSellingTo();
        $productData->sellingDenied = $product->isSellingDenied();
        $productData->unit = $product->getUnit();

        $productData->hidden = $product->isHidden();
        $productData->categoriesByDomainId = $product->getCategoriesIndexedByDomainId();
        $productData->brand = $product->getBrand();

        $productData->parameters = $this->getParametersData($product);

        try {
            $productData->manualInputPricesByPricingGroupId = $this->productInputPriceFacade->getManualInputPricesDataIndexedByPricingGroupId(
                $product,
            );
        } catch (MainVariantPriceCalculationException) {
            $productData->manualInputPricesByPricingGroupId = $this->getNullForAllPricingGroups();
        }
        $productData->accessories = $this->getAccessoriesData($product);
        $productData->images = $this->imageUploadDataFactory->createFromEntityAndType($product);
        $productData->variants = $product->getVariants();
        $productData->pluginData = $this->pluginDataFormExtensionFacade->getAllData('product', $product->getId());
        $productData->weight = $product->getWeight();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    protected function getAccessoriesData(Product $product)
    {
        $productAccessoriesByPosition = [];

        foreach ($this->productAccessoryRepository->getAllByProduct($product) as $productAccessory) {
            $productAccessoriesByPosition[$productAccessory->getPosition()] = $productAccessory->getAccessory();
        }

        return $productAccessoriesByPosition;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData[]
     */
    protected function getParametersData(Product $product)
    {
        $productParameterValuesData = [];
        $productParameterValues = $this->parameterRepository->getProductParameterValuesByProduct($product);

        foreach ($productParameterValues as $productParameterValue) {
            $productParameterValuesData[] = $this->productParameterValueDataFactory->createFromProductParameterValue(
                $productParameterValue,
            );
        }

        return $productParameterValuesData;
    }

    /**
     * @return array
     */
    protected function getNullForAllDomains()
    {
        $nullForAllDomains = [];

        foreach ($this->domain->getAll() as $domainConfig) {
            $nullForAllDomains[$domainConfig->getId()] = null;
        }

        return $nullForAllDomains;
    }

    /**
     * @return null[]
     */
    protected function getNullForAllPricingGroups()
    {
        $inputPrices = [];

        foreach ($this->pricingGroupFacade->getAll() as $pricingGroup) {
            $inputPrices[$pricingGroup->getId()] = null;
        }

        return $inputPrices;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $productData
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     */
    protected function fillProductStockByProduct(ProductData $productData, Product $product): void
    {
        $this->fillProductStockByStocks($productData);

        foreach ($this->productStockFacade->getProductStocksByProduct($product) as $productStock) {
            $productData->productStockData[$productStock->getStock()->getId()] = $this->productStockDataFactory->createFromProductStock($productStock);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $productData
     */
    protected function fillProductStockByStocks(ProductData $productData): void
    {
        foreach ($this->stockFacade->getAllStocks() as $stock) {
            $productData->productStockData[$stock->getId()] = $this->productStockDataFactory->createFromStock($stock);
        }
    }
}
