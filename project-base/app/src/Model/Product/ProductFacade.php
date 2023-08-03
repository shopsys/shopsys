<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\Model\Category\Category as AppCategory;
use App\Model\ProductVideo\ProductVideoFacade;
use App\Model\Stock\ProductStockData;
use App\Model\Stock\ProductStockFacade;
use App\Model\Stock\StockFacade;
use App\Model\Store\ProductStoreData;
use App\Model\Store\ProductStoreFacade;
use App\Model\Store\StoreFacade;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportScheduler;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomainFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade as BaseProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductHiddenRecalculator;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFactoryInterface;

/**
 * @property \App\Model\Product\ProductRepository $productRepository
 * @property \App\Model\Product\Parameter\ParameterRepository $parameterRepository
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @property \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
 * @property \App\Model\Product\ProductHiddenRecalculator $productHiddenRecalculator
 * @property \App\Model\Product\ProductSellingDeniedRecalculator $productSellingDeniedRecalculator
 * @property \App\Model\Product\Availability\AvailabilityFacade $availabilityFacade
 * @property \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation $productPriceCalculation
 * @method \App\Model\Product\Product getById(int $productId)
 * @method \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductSellingPrice[][] getAllProductSellingPricesIndexedByDomainId(\App\Model\Product\Product $product)
 * @method \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductSellingPrice[] getAllProductSellingPricesByDomainId(\App\Model\Product\Product $product, int $domainId)
 * @method refreshProductManualInputPrices(\App\Model\Product\Product $product, \Shopsys\FrameworkBundle\Component\Money\Money[]|null[] $manualInputPrices)
 * @method createProductVisibilities(\App\Model\Product\Product $product)
 * @method \App\Model\Product\Product getOneByCatnumExcludeMainVariants(string $productCatnum)
 * @method \App\Model\Product\Product getByUuid(string $uuid)
 * @method markProductsForExport(\App\Model\Product\Product[] $products)
 * @method \App\Model\Product\Product[] getProductsWithAvailability(\Shopsys\FrameworkBundle\Model\Product\Availability\Availability $availability)
 * @method \App\Model\Product\Product[] getProductsWithParameter(\App\Model\Product\Parameter\Parameter $parameter)
 * @method \App\Model\Product\Product[] getProductsWithBrand(\App\Model\Product\Brand\Brand $brand)
 * @method \App\Model\Product\Product[] getProductsWithFlag(\App\Model\Product\Flag\Flag $flag)
 * @method \App\Model\Product\Product[] getProductsWithUnit(\App\Model\Product\Unit\Unit $unit)
 * @method createFriendlyUrlsWhenRenamed(\App\Model\Product\Product $product, array $originalNames)
 * @method array getChangedNamesByLocale(\App\Model\Product\Product $product, array $originalNames)
 */
class ProductFacade extends BaseProductFacade
{
    public const ASSETS_FILE_TYPE = '.pdf';

    /**
     * @param string $productFilesUrlPrefix
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $em
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \App\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler $productPriceRecalculationScheduler
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository $pricingGroupRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade $productManualInputPriceFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Model\Product\ProductHiddenRecalculator $productHiddenRecalculator
     * @param \App\Model\Product\ProductSellingDeniedRecalculator $productSellingDeniedRecalculator
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginCrudExtensionFacade
     * @param \App\Model\Product\ProductFactory $productFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFactory $productAccessoryFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomainFactory $productCategoryDomainFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueFactory $productParameterValueFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFactory $productVisibilityFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation $productPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportScheduler $productExportScheduler
     * @param \App\Model\Stock\ProductStockFacade $productStockFacade
     * @param \App\Model\Stock\StockFacade $stockFacade
     * @param \App\Model\Store\ProductStoreFacade $productStoreFacade
     * @param \App\Model\Store\StoreFacade $storeFacade
     * @param \App\Model\ProductVideo\ProductVideoFacade $productVideoFacade
     */
    public function __construct(
        private string $productFilesUrlPrefix,
        EntityManagerInterface $em,
        ProductRepository $productRepository,
        ProductVisibilityFacade $productVisibilityFacade,
        ParameterRepository $parameterRepository,
        Domain $domain,
        ImageFacade $imageFacade,
        ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
        PricingGroupRepository $pricingGroupRepository,
        ProductManualInputPriceFacade $productManualInputPriceFacade,
        ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler,
        FriendlyUrlFacade $friendlyUrlFacade,
        ProductHiddenRecalculator $productHiddenRecalculator,
        ProductSellingDeniedRecalculator $productSellingDeniedRecalculator,
        ProductAccessoryRepository $productAccessoryRepository,
        PluginCrudExtensionFacade $pluginCrudExtensionFacade,
        ProductFactoryInterface $productFactory,
        ProductAccessoryFactoryInterface $productAccessoryFactory,
        ProductCategoryDomainFactoryInterface $productCategoryDomainFactory,
        ProductParameterValueFactoryInterface $productParameterValueFactory,
        ProductVisibilityFactoryInterface $productVisibilityFactory,
        ProductPriceCalculation $productPriceCalculation,
        ProductExportScheduler $productExportScheduler,
        private ProductStockFacade $productStockFacade,
        private StockFacade $stockFacade,
        private ProductStoreFacade $productStoreFacade,
        private StoreFacade $storeFacade,
        private readonly ProductVideoFacade $productVideoFacade,
    ) {
        parent::__construct(
            $em,
            $productRepository,
            $productVisibilityFacade,
            $parameterRepository,
            $domain,
            $imageFacade,
            $productPriceRecalculationScheduler,
            $pricingGroupRepository,
            $productManualInputPriceFacade,
            $productAvailabilityRecalculationScheduler,
            $friendlyUrlFacade,
            $productHiddenRecalculator,
            $productSellingDeniedRecalculator,
            $productAccessoryRepository,
            $pluginCrudExtensionFacade,
            $productFactory,
            $productAccessoryFactory,
            $productCategoryDomainFactory,
            $productParameterValueFactory,
            $productVisibilityFactory,
            $productPriceCalculation,
            $productExportScheduler,
        );
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @return \App\Model\Product\Product
     */
    public function create(ProductData $productData)
    {
        /** @var \App\Model\Product\Product $product */
        $product = parent::create($productData);

        $this->editProductStockAndStoreRelation($productData, $product);

        $this->productSellingDeniedRecalculator->calculateSellingDeniedForProduct($product);

        return $product;
    }

    /**
     * @param string $productCatnum
     * @return \App\Model\Product\Product|null
     */
    public function findOneByCatnumExcludeMainVariants($productCatnum): ?BaseProduct
    {
        try {
            /** @var \App\Model\Product\Product $product */
            $product = $this->productRepository->getOneByCatnumExcludeMainVariants($productCatnum);

            return $product;
        } catch (ProductNotFoundException $exception) {
            return null;
        }
    }

    /**
     * @param int $productId
     * @param \App\Model\Product\ProductData $productData
     * @return \App\Model\Product\Product
     */
    public function edit($productId, ProductData $productData)
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->productRepository->getById($productId);

        $productCategoryDomains = $this->productCategoryDomainFactory->createMultiple($product, $productData->categoriesByDomainId);
        $product->edit($productCategoryDomains, $productData);
        $this->productPriceRecalculationScheduler->scheduleProductForImmediateRecalculation($product);

        $this->saveParameters($product, $productData->parameters);

        if (!$product->isMainVariant()) {
            $this->refreshProductManualInputPrices($product, $productData->manualInputPricesByPricingGroupId);
        }

        if ($product->isMainVariant()) {
            $product->refreshVariants($productData->variants);
        }

        if ($product->isVariant() === true) {
            $product->getMainVariant()->markForExport();
        }
        $this->refreshProductAccessories($product, $productData->accessories);
        $this->em->flush();
        $this->imageFacade->manageImages($product, $productData->images);
        $this->productHiddenRecalculator->calculateHiddenForProduct($product);
        $this->friendlyUrlFacade->saveUrlListFormData('front_product_detail', $product->getId(), $productData->urls);
        $this->friendlyUrlFacade->createFriendlyUrls('front_product_detail', $product->getId(), $product->getFullnames());

        $this->pluginCrudExtensionFacade->saveAllData('product', $product->getId(), $productData->pluginData);

        $this->productAvailabilityRecalculationScheduler->scheduleProductForImmediateRecalculation($product);
        $this->productVisibilityFacade->refreshProductsVisibilityForMarkedDelayed();
        $this->productPriceRecalculationScheduler->scheduleProductForImmediateRecalculation($product);

        $this->productExportScheduler->scheduleRowIdForImmediateExport($product->getId());

        if ($product->isMainVariant()) {
            foreach ($product->getVariants() as $variant) {
                $this->productExportScheduler->scheduleRowIdForImmediateExport($variant->getId());
            }
        } elseif ($product->isVariant()) {
            $this->productExportScheduler->scheduleRowIdForImmediateExport($product->getMainVariant()->getId());
        }

        $this->editProductStockAndStoreRelation($productData, $product);

        $this->productSellingDeniedRecalculator->calculateSellingDeniedForProduct($product);

        $this->productVideoFacade->saveProductVideosToProduct($product, $productData->productVideosData);

        return $product;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Product\ProductFilesData $productFilesData
     */
    public function editProductFileAttributes(BaseProduct $product, ProductFilesData $productFilesData): void
    {
        $product->editFileAttributes($productFilesData);
        $this->em->flush();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Product\ProductData $productData
     */
    public function setAdditionalDataAfterCreate(BaseProduct $product, ProductData $productData)
    {
        // Persist of ProductCategoryDomain requires known primary key of Product
        // @see https://github.com/doctrine/doctrine2/issues/4869
        $productCategoryDomains = $this->productCategoryDomainFactory->createMultiple($product, $productData->categoriesByDomainId);
        $product->setProductCategoryDomains($productCategoryDomains);
        $this->em->flush();

        $this->saveParameters($product, $productData->parameters);
        $this->createProductVisibilities($product);
        $this->refreshProductManualInputPrices($product, $productData->manualInputPricesByPricingGroupId);
        $this->refreshProductAccessories($product, $productData->accessories);
        $this->imageFacade->manageImages($product, $productData->images);
        $this->productHiddenRecalculator->calculateHiddenForProduct($product);

        $this->friendlyUrlFacade->saveUrlListFormData('front_product_detail', $product->getId(), $productData->urls);
        $this->friendlyUrlFacade->createFriendlyUrls('front_product_detail', $product->getId(), $product->getNames());

        $this->productAvailabilityRecalculationScheduler->scheduleProductForImmediateRecalculation($product);
        $this->productVisibilityFacade->refreshProductsVisibilityForMarkedDelayed();
        $this->productPriceRecalculationScheduler->scheduleProductForImmediateRecalculation($product);
    }

    /**
     * @param string $fileName
     * @param string $domainUrl
     * @param string|null $browserCacheCleanerSuffix
     * @return string
     */
    public function getProductTransferredFileUrl(
        string $fileName,
        string $domainUrl,
        ?string $browserCacheCleanerSuffix = null,
    ): string {
        return $domainUrl . $this->productFilesUrlPrefix . $fileName . ($browserCacheCleanerSuffix !== null ? '?' . md5($browserCacheCleanerSuffix) : '');
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return array
     */
    public function getDownloadFilesForProductByDomainConfig(BaseProduct $product, DomainConfig $domainConfig): array
    {
        $downloadFileUrls = [];

        if ($product->isDownloadAssemblyInstructionFiles() === false && $product->getAssemblyInstructionCode($domainConfig->getId()) !== null) {
            $url = $this->getProductTransferredFileUrl(
                $product->getProductFileNameByType(
                    $domainConfig->getId(),
                    Product::FILE_IDENTIFICATOR_ASSEMBLY_INSTRUCTION_TYPE,
                ),
                $domainConfig->getUrl(),
                $product->getAssemblyInstructionCode($domainConfig->getId()),
            );
            $downloadFileUrls[] = [
                'anchor_text' => t('Installation manual'),
                'url' => $url,
            ];
        }

        if ($product->isDownloadProductTypePlanFiles() === false && $product->getProductTypePlanCode($domainConfig->getId()) !== null) {
            $url = $this->getProductTransferredFileUrl(
                $product->getProductFileNameByType(
                    $domainConfig->getId(),
                    Product::FILE_IDENTIFICATOR_PRODUCT_TYPE_PLAN_TYPE,
                ),
                $domainConfig->getUrl(),
                $product->getProductTypePlanCode($domainConfig->getId()),
            );
            $downloadFileUrls[] = [
                'anchor_text' => t('Type plan'),
                'url' => $url,
            ];
        }

        return $downloadFileUrls;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData[] $productParameterValuesData
     */
    protected function saveParameters(BaseProduct $product, array $productParameterValuesData)
    {

        // Doctrine runs INSERTs before DELETEs in UnitOfWork. In case of UNIQUE constraint
        // in database, this leads in trying to insert duplicate entry.
        // That's why it's necessary to do remove and flush first.

        $oldProductParameterValues = $this->parameterRepository->getProductParameterValuesByProduct($product);

        foreach ($oldProductParameterValues as $oldProductParameterValue) {
            $this->em->remove($oldProductParameterValue);
        }
        $this->em->flush();

        $toFlush = [];

        foreach ($productParameterValuesData as $productParameterValueData) {
            /** @var \App\Model\Product\Parameter\ParameterValueData $parameterValueData */
            $parameterValueData = $productParameterValueData->parameterValueData;
            $parameterValue = $this->parameterRepository->findOrCreateParameterValueByParameterValueData(
                $parameterValueData,
            );

            $productParameterValue = $this->productParameterValueFactory->create(
                $product,
                $productParameterValueData->parameter,
                $parameterValue,
            );
            $this->em->persist($productParameterValue);
            $toFlush[] = $productParameterValue;
        }

        if (count($toFlush) > 0) {
            $this->em->flush();
        }
    }

    /**
     * @param string $catnum
     * @return \App\Model\Product\Product|null
     */
    public function findByCatnum(string $catnum): ?BaseProduct
    {
        return $this->productRepository->findByCatnum($catnum);
    }

    /**
     * @param array $catnums
     * @return \App\Model\Product\Product[]
     */
    public function findAllByCatnums(array $catnums): array
    {
        return $this->productRepository->findAllByCatnums($catnums);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Product\Product[] $accessories
     */
    public function refreshProductAccessories(BaseProduct $product, array $accessories): void
    {
        parent::refreshProductAccessories($product, $accessories);
    }

    /**
     * @param \App\Model\Category\Category $category
     * @return \App\Model\Product\Product[]
     */
    public function getProductsByCategory(AppCategory $category): array
    {
        return $this->productRepository->getProductsByCategory($category);
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product $product
     */
    public function editProductStockAndStoreRelation(ProductData $productData, Product $product): void
    {
        $this->editProductStockRelation($productData, $product);
        $this->editProductStoreRelation($productData, $product);
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product $product
     */
    private function editProductStockRelation(ProductData $productData, Product $product): void
    {
        $stockIds = array_map(
            fn (ProductStockData $productStockData): int => $productStockData->stockId,
            $productData->stockProductData,
        );

        $stocksIndexedById = $this->stockFacade->getStocksByIdsIndexedById($stockIds);

        $this->productStockFacade->editProductStockRelations(
            $product,
            $stocksIndexedById,
            $productData->stockProductData,
        );
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product $product
     */
    private function editProductStoreRelation(ProductData $productData, Product $product): void
    {
        $storeIds = array_map(
            fn (ProductStoreData $productStoreData): int => $productStoreData->storeId,
            $productData->productStoreData,
        );

        $storesIndexedById = $this->storeFacade->getStoresByIdsIndexedById($storeIds);

        $this->productStoreFacade->editProductStoreRelations(
            $product,
            $storesIndexedById,
            $productData->productStoreData,
        );
    }
}
