<?php

namespace Shopsys\FrameworkBundle\Model\Product;

use BadMethodCallException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Pricing\Exception\MainVariantPriceCalculationException;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;

class ProductDataFactory implements ProductDataFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     */
    protected $vatFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade
     */
    protected $productInputPriceFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade
     */
    protected $unitFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository
     */
    protected $parameterRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    protected $friendlyUrlFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository
     */
    protected $productAccessoryRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    protected $imageFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade
     */
    protected $pluginDataFormExtensionFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactoryInterface
     */
    protected $productParameterValueDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade
     */
    protected $pricingGroupFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade
     */
    protected $availabilityFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade $productInputPriceFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginDataFormExtensionFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactoryInterface $productParameterValueDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade $pricingGroupFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade|null $availabilityFacade
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
        ?AvailabilityFacade $availabilityFacade = null
    ) {
        $this->vatFacade = $vatFacade;
        $this->productInputPriceFacade = $productInputPriceFacade;
        $this->unitFacade = $unitFacade;
        $this->domain = $domain;
        $this->productRepository = $productRepository;
        $this->parameterRepository = $parameterRepository;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->productAccessoryRepository = $productAccessoryRepository;
        $this->imageFacade = $imageFacade;
        $this->pluginDataFormExtensionFacade = $pluginDataFormExtensionFacade;
        $this->productParameterValueDataFactory = $productParameterValueDataFactory;
        $this->pricingGroupFacade = $pricingGroupFacade;
        $this->availabilityFacade = $availabilityFacade;
    }

    /**
     * @required
     * @internal This function will be replaced by constructor injection in next major
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade $availabilityFacade
     */
    public function setAvailabilityFacade(AvailabilityFacade $availabilityFacade)
    {
        if ($this->availabilityFacade !== null && $this->availabilityFacade !== $availabilityFacade) {
            throw new BadMethodCallException(
                sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__)
            );
        }
        if ($this->availabilityFacade !== null) {
            return;
        }

        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
        $this->availabilityFacade = $availabilityFacade;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductData
     */
    protected function createInstance(): ProductData
    {
        return new ProductData();
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
    protected function fillNew(ProductData $productData)
    {
        $productVatsIndexedByDomain = [];
        foreach ($this->domain->getAllIds() as $domainId) {
            $productVatsIndexedByDomain[$domainId] = $this->vatFacade->getDefaultVatForDomain($domainId);
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
        $productData->availability = $this->availabilityFacade->getDefaultInStockAvailability();
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
    protected function fillFromProduct(ProductData $productData, Product $product)
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

            $mainFriendlyUrl = $this->friendlyUrlFacade->findMainFriendlyUrl(
                $domainId,
                'front_product_detail',
                $product->getId()
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
        $productData->flags = $product->getFlags();
        $productData->usingStock = $product->isUsingStock();
        $productData->stockQuantity = $product->getStockQuantity();
        $productData->unit = $product->getUnit();
        $productData->availability = $product->getAvailability();
        $productData->outOfStockAvailability = $product->getOutOfStockAvailability();
        $productData->outOfStockAction = $product->getOutOfStockAction();

        $productData->hidden = $product->isHidden();
        $productData->categoriesByDomainId = $product->getCategoriesIndexedByDomainId();
        $productData->brand = $product->getBrand();
        $productData->orderingPriority = $product->getOrderingPriority();

        $productData->parameters = $this->getParametersData($product);
        try {
            $productData->manualInputPricesByPricingGroupId = $this->productInputPriceFacade->getManualInputPricesDataIndexedByPricingGroupId(
                $product
            );
        } catch (MainVariantPriceCalculationException $ex) {
            $productData->manualInputPricesByPricingGroupId = $this->getNullForAllPricingGroups();
        }
        $productData->accessories = $this->getAccessoriesData($product);
        $productData->images->orderedImages = $this->imageFacade->getImagesByEntityIndexedById($product, null);
        $productData->variants = $product->getVariants();
        $productData->pluginData = $this->pluginDataFormExtensionFacade->getAllData('product', $product->getId());
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
                $productParameterValue
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
}
