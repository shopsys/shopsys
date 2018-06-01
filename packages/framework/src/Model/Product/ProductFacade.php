<?php

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculator;

class ProductFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade
     */
    protected $productVisibilityFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository
     */
    protected $parameterRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductService
     */
    protected $productService;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    protected $imageFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler
     */
    protected $productPriceRecalculationScheduler;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository
     */
    protected $pricingGroupRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade
     */
    protected $productManualInputPriceFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler
     */
    protected $productAvailabilityRecalculationScheduler;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    protected $friendlyUrlFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductHiddenRecalculator
     */
    protected $productHiddenRecalculator;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator
     */
    protected $productSellingDeniedRecalculator;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository
     */
    protected $productAccessoryRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductVariantService
     */
    protected $productVariantService;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade
     */
    protected $availabilityFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade
     */
    protected $pluginCrudExtensionFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFactoryInterface
     */
    protected $productFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFactoryInterface
     */
    protected $productAccessoryFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomainFactoryInterface
     */
    protected $productCategoryDomainFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductDomainFactoryInterface
     */
    protected $productDomainFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueFactoryInterface
     */
    protected $productParameterValueFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFactoryInterface
     */
    protected $productVisibilityFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculator
     */
    protected $productPriceRecalculator;

    public function __construct(
        EntityManagerInterface $em,
        ProductRepository $productRepository,
        ProductVisibilityFacade $productVisibilityFacade,
        ParameterRepository $parameterRepository,
        Domain $domain,
        ProductService $productService,
        ImageFacade $imageFacade,
        ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
        PricingGroupRepository $pricingGroupRepository,
        ProductManualInputPriceFacade $productManualInputPriceFacade,
        ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler,
        FriendlyUrlFacade $friendlyUrlFacade,
        ProductHiddenRecalculator $productHiddenRecalculator,
        ProductSellingDeniedRecalculator $productSellingDeniedRecalculator,
        ProductAccessoryRepository $productAccessoryRepository,
        ProductVariantService $productVariantService,
        AvailabilityFacade $availabilityFacade,
        PluginCrudExtensionFacade $pluginCrudExtensionFacade,
        ProductFactoryInterface $productFactory,
        ProductAccessoryFactoryInterface $productAccessoryFactory,
        ProductCategoryDomainFactoryInterface $productCategoryDomainFactory,
        ProductDomainFactoryInterface $productDomainFactory,
        ProductParameterValueFactoryInterface $productParameterValueFactory,
        ProductVisibilityFactoryInterface $productVisibilityFactory,
        ProductPriceRecalculator $productPriceRecalculator
    ) {
        $this->em = $em;
        $this->productRepository = $productRepository;
        $this->productVisibilityFacade = $productVisibilityFacade;
        $this->parameterRepository = $parameterRepository;
        $this->domain = $domain;
        $this->productService = $productService;
        $this->imageFacade = $imageFacade;
        $this->productPriceRecalculationScheduler = $productPriceRecalculationScheduler;
        $this->pricingGroupRepository = $pricingGroupRepository;
        $this->productManualInputPriceFacade = $productManualInputPriceFacade;
        $this->productAvailabilityRecalculationScheduler = $productAvailabilityRecalculationScheduler;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->productHiddenRecalculator = $productHiddenRecalculator;
        $this->productSellingDeniedRecalculator = $productSellingDeniedRecalculator;
        $this->productAccessoryRepository = $productAccessoryRepository;
        $this->productVariantService = $productVariantService;
        $this->availabilityFacade = $availabilityFacade;
        $this->pluginCrudExtensionFacade = $pluginCrudExtensionFacade;
        $this->productFactory = $productFactory;
        $this->productAccessoryFactory = $productAccessoryFactory;
        $this->productCategoryDomainFactory = $productCategoryDomainFactory;
        $this->productDomainFactory = $productDomainFactory;
        $this->productParameterValueFactory = $productParameterValueFactory;
        $this->productVisibilityFactory = $productVisibilityFactory;
        $this->productPriceRecalculator = $productPriceRecalculator;
    }

    /**
     * @param int $productId
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getById($productId)
    {
        return $this->productRepository->getById($productId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductEditData $productEditData
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function create(ProductEditData $productEditData)
    {
        $product = $this->productFactory->create($productEditData->productData);

        if ($product->isUsingStock()) {
            $defaultInStockAvailability = $this->availabilityFacade->getDefaultInStockAvailability();
            $product->setCalculatedAvailability($defaultInStockAvailability);
            $product->markForAvailabilityRecalculation();
        }

        $this->em->persist($product);
        $this->em->flush($product);
        $this->setAdditionalDataAfterCreate($product, $productEditData);

        $this->pluginCrudExtensionFacade->saveAllData('product', $product->getId(), $productEditData->pluginData);

        return $product;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductEditData $productEditData
     */
    public function setAdditionalDataAfterCreate(Product $product, ProductEditData $productEditData)
    {
        // Persist of ProductCategoryDomain requires known primary key of Product
        // @see https://github.com/doctrine/doctrine2/issues/4869
        $product->setCategories($this->productCategoryDomainFactory, $productEditData->productData->categoriesByDomainId);
        $this->em->flush($product);

        $this->saveParameters($product, $productEditData->parameters);
        $this->createProductDomains($product, $this->domain->getAll());
        $this->createProductVisibilities($product);
        $this->refreshProductDomains($product, $productEditData);
        $this->refreshProductManualInputPrices($product, $productEditData->manualInputPricesByPricingGroupId);
        $this->refreshProductAccessories($product, $productEditData->accessories);
        $this->productHiddenRecalculator->calculateHiddenForProduct($product);
        $this->productSellingDeniedRecalculator->calculateSellingDeniedForProduct($product);

        $this->imageFacade->uploadImages($product, $productEditData->images->uploadedFiles, null);
        $this->friendlyUrlFacade->createFriendlyUrls('front_product_detail', $product->getId(), $product->getNames());

        $this->productAvailabilityRecalculationScheduler->scheduleProductForImmediateRecalculation($product);
        $this->productVisibilityFacade->refreshProductsVisibilityForMarkedDelayed();
        $this->productPriceRecalculator->recalculateProductPrices($product);
    }

    /**
     * @param int $productId
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductEditData $productEditData
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function edit($productId, ProductEditData $productEditData)
    {
        $product = $this->productRepository->getById($productId);

        $this->productService->edit($product, $productEditData->productData);

        $this->saveParameters($product, $productEditData->parameters);
        $this->refreshProductDomains($product, $productEditData);
        if (!$product->isMainVariant()) {
            $this->refreshProductManualInputPrices($product, $productEditData->manualInputPricesByPricingGroupId);
        } else {
            $this->productVariantService->refreshProductVariants($product, $productEditData->variants);
        }
        $this->refreshProductAccessories($product, $productEditData->accessories);
        $this->em->flush();
        $this->productHiddenRecalculator->calculateHiddenForProduct($product);
        $this->productSellingDeniedRecalculator->calculateSellingDeniedForProduct($product);
        $this->imageFacade->saveImageOrdering($productEditData->images->orderedImages);
        $this->imageFacade->uploadImages($product, $productEditData->images->uploadedFiles, null);
        $this->imageFacade->deleteImages($product, $productEditData->images->imagesToDelete);
        $this->friendlyUrlFacade->saveUrlListFormData('front_product_detail', $product->getId(), $productEditData->urls);
        $this->friendlyUrlFacade->createFriendlyUrls('front_product_detail', $product->getId(), $product->getNames());

        $this->pluginCrudExtensionFacade->saveAllData('product', $product->getId(), $productEditData->pluginData);

        $this->productAvailabilityRecalculationScheduler->scheduleProductForImmediateRecalculation($product);
        $this->productVisibilityFacade->refreshProductsVisibilityForMarkedDelayed();
        $this->productPriceRecalculator->recalculateProductPrices($product);

        return $product;
    }

    /**
     * @param int $productId
     */
    public function delete($productId)
    {
        $product = $this->productRepository->getById($productId);
        $productDeleteResult = $this->productService->delete($product);
        $productsForRecalculations = $productDeleteResult->getProductsForRecalculations();
        foreach ($productsForRecalculations as $productForRecalculations) {
            $this->productPriceRecalculator->recalculateProductPrices($productForRecalculations);
            $productForRecalculations->markProductForVisibilityRecalculation();
            $this->productAvailabilityRecalculationScheduler->scheduleProductForImmediateRecalculation($productForRecalculations);
        }
        $this->em->remove($product);
        $this->em->flush();

        $this->pluginCrudExtensionFacade->removeAllData('product', $product->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData[] $productParameterValuesData
     */
    protected function saveParameters(Product $product, array $productParameterValuesData)
    {
        // Doctrine runs INSERTs before DELETEs in UnitOfWork. In case of UNIQUE constraint
        // in database, this leads in trying to insert duplicate entry.
        // That's why it's necessary to do remove and flush first.

        $oldProductParameterValues = $this->parameterRepository->getProductParameterValuesByProduct($product);
        foreach ($oldProductParameterValues as $oldProductParameterValue) {
            $this->em->remove($oldProductParameterValue);
        }
        $this->em->flush($oldProductParameterValues);

        $toFlush = [];
        foreach ($productParameterValuesData as $productParameterValueData) {
            $productParameterValue = $this->productParameterValueFactory->create(
                $product,
                $productParameterValueData->parameter,
                $this->parameterRepository->findOrCreateParameterValueByValueTextAndLocale(
                    $productParameterValueData->parameterValueData->text,
                    $productParameterValueData->parameterValueData->locale
                )
            );
            $this->em->persist($productParameterValue);
            $toFlush[] = $productParameterValue;
        }
        $this->em->flush($toFlush);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[] $domains
     */
    protected function createProductDomains(Product $product, array $domains)
    {
        $toFlush = [];
        foreach ($domains as $domain) {
            $productDomain = $this->productDomainFactory->create($product, $domain->getId());
            $this->em->persist($productDomain);
            $toFlush[] = $productDomain;
        }
        $this->em->flush($toFlush);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductEditData $productEditData
     */
    protected function refreshProductDomains(Product $product, ProductEditData $productEditData)
    {
        $productDomains = $this->productRepository->getProductDomainsByProductIndexedByDomainId($product);
        $seoTitles = $productEditData->seoTitles;
        $seoMetaDescriptions = $productEditData->seoMetaDescriptions;
        $seoH1s = $productEditData->seoH1s;
        if (!$product->isVariant()) {
            $descriptions = $productEditData->descriptions;
            $shortDescriptions = $productEditData->shortDescriptions;
        }

        foreach ($productDomains as $domainId => $productDomain) {
            if (!empty($seoTitles)) {
                $productDomain->setSeoTitle($seoTitles[$domainId]);
            }
            if (!empty($seoMetaDescriptions)) {
                $productDomain->setSeoMetaDescription($seoMetaDescriptions[$domainId]);
            }
            if (!empty($descriptions)) {
                $productDomain->setDescription($descriptions[$domainId]);
            }
            if (!empty($shortDescriptions)) {
                $productDomain->setShortDescription($shortDescriptions[$domainId]);
            }
            if (!empty($seoH1s)) {
                $productDomain->setSeoH1($seoH1s[$domainId]);
            }
        }

        $this->em->flush($productDomains);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductSellingPrice[]
     */
    public function getAllProductSellingPricesIndexedByDomainId(Product $product)
    {
        return $this->productService->getProductSellingPricesIndexedByDomainIdAndPricingGroupId(
            $product,
            $this->pricingGroupRepository->getAll()
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param string[] $manualInputPrices
     */
    protected function refreshProductManualInputPrices(Product $product, array $manualInputPrices)
    {
        if ($product->getPriceCalculationType() === Product::PRICE_CALCULATION_TYPE_MANUAL) {
            foreach ($this->pricingGroupRepository->getAll() as $pricingGroup) {
                $this->productManualInputPriceFacade->refresh($product, $pricingGroup, $manualInputPrices[$pricingGroup->getId()]);
            }
        } else {
            $this->productManualInputPriceFacade->deleteByProduct($product);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     */
    protected function createProductVisibilities(Product $product)
    {
        $toFlush = [];
        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();
            foreach ($this->pricingGroupRepository->getPricingGroupsByDomainId($domainId) as $pricingGroup) {
                $productVisibility = $this->productVisibilityFactory->create($product, $pricingGroup, $domainId);
                $this->em->persist($productVisibility);
                $toFlush[] = $productVisibility;
            }
        }
        $this->em->flush($toFlush);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $accessories
     */
    protected function refreshProductAccessories(Product $product, array $accessories)
    {
        $oldProductAccessories = $this->productAccessoryRepository->getAllByProduct($product);
        foreach ($oldProductAccessories as $oldProductAccessory) {
            $this->em->remove($oldProductAccessory);
        }
        $this->em->flush($oldProductAccessories);

        $toFlush = [];
        foreach ($accessories as $position => $accessory) {
            $newProductAccessory = $this->productAccessoryFactory->create($product, $accessory, $position);
            $this->em->persist($newProductAccessory);
            $toFlush[] = $newProductAccessory;
        }
        $this->em->flush($toFlush);
    }

    /**
     * @param string $productCatnum
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getOneByCatnumExcludeMainVariants($productCatnum)
    {
        return $this->productRepository->getOneByCatnumExcludeMainVariants($productCatnum);
    }
}
