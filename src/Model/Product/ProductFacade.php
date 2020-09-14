<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use App\Model\Product\Exception\DeleteDefaultVariantException;
use App\Model\Stock\ProductStockFacade;
use App\Model\Stock\StockFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportScheduler;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Product\Product;
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
 * @property \App\Model\Product\Pricing\ProductPriceCalculation $productPriceCalculation
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
 * @method \App\Model\Product\Product[] getProductsWithUnit(\Shopsys\FrameworkBundle\Model\Product\Unit\Unit $unit)
 * @property \App\Model\Product\Parameter\ParameterRepository $parameterRepository
 * @property \App\Model\Product\ProductRepository $productRepository
 * @property \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $em
 * @property \App\Model\Product\ProductHiddenRecalculator $productHiddenRecalculator
 * @property \App\Model\Product\ProductSellingDeniedRecalculator $productSellingDeniedRecalculator
 * @property \App\Model\Product\Availability\AvailabilityFacade $availabilityFacade
 * @property \App\Component\Domain\Domain $domain
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @method \App\Model\Product\Product getSellableByUuid(string $uuid, int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @property \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
 * @method createFriendlyUrlsWhenRenamed(\App\Model\Product\Product $product, array $originalNames)
 * @method array getChangedNamesByLocale(\App\Model\Product\Product $product, array $originalNames)
 */
class ProductFacade extends BaseProductFacade
{
    public const ASSETS_FILE_TYPE = '.pdf';
    public const PRODUCT_PATH_PREFIX = 'produkt';

    /**
     * @var \App\Model\Stock\StockFacade
     */
    private $stockFacade;

    /**
     * @var \App\Model\Stock\ProductStockFacade
     */
    private $productStockFacade;

    /**
     * @var string
     */
    private $productFilesUrlPrefix;
    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlRepository
     */
    private FriendlyUrlRepository $friendlyUrlRepository;

    /**
     * @param string $productFilesUrlPrefix
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $em
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler $productPriceRecalculationScheduler
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository $pricingGroupRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade $productManualInputPriceFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Model\Product\ProductHiddenRecalculator $productHiddenRecalculator
     * @param \App\Model\Product\ProductSellingDeniedRecalculator $productSellingDeniedRecalculator
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository
     * @param \App\Model\Product\Availability\AvailabilityFacade $availabilityFacade
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginCrudExtensionFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFactoryInterface $productFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFactoryInterface $productAccessoryFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomainFactoryInterface $productCategoryDomainFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueFactoryInterface $productParameterValueFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFactoryInterface $productVisibilityFactory
     * @param \App\Model\Product\Pricing\ProductPriceCalculation $productPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportScheduler $productExportScheduler
     * @param \App\Model\Stock\ProductStockFacade $productStockFacade
     * @param \App\Model\Stock\StockFacade $stockFacade
     */
    public function __construct(
        string $productFilesUrlPrefix,
        EntityManagerDecorator $em,
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
        AvailabilityFacade $availabilityFacade,
        PluginCrudExtensionFacade $pluginCrudExtensionFacade,
        ProductFactoryInterface $productFactory,
        ProductAccessoryFactoryInterface $productAccessoryFactory,
        ProductCategoryDomainFactoryInterface $productCategoryDomainFactory,
        ProductParameterValueFactoryInterface $productParameterValueFactory,
        ProductVisibilityFactoryInterface $productVisibilityFactory,
        ProductPriceCalculation $productPriceCalculation,
        ProductExportScheduler $productExportScheduler,
        ProductStockFacade $productStockFacade,
        StockFacade $stockFacade,
        FriendlyUrlRepository $friendlyUrlRepository
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
            $availabilityFacade,
            $pluginCrudExtensionFacade,
            $productFactory,
            $productAccessoryFactory,
            $productCategoryDomainFactory,
            $productParameterValueFactory,
            $productVisibilityFactory,
            $productPriceCalculation,
            $productExportScheduler
        );
        $this->stockFacade = $stockFacade;
        $this->productStockFacade = $productStockFacade;
        $this->productFilesUrlPrefix = $productFilesUrlPrefix;
        $this->friendlyUrlRepository = $friendlyUrlRepository;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @return \App\Model\Product\Product
     */
    public function create(ProductData $productData)
    {
        /** @var \App\Model\Product\Product $product */
        $product = parent::create($productData);

        foreach ($productData->stockProductData as $productStockData) {
            $stock = $this->stockFacade->getById($productStockData->stockId);
            $this->productStockFacade->editProductStockRelation($product, $stock, $productStockData);
        }

        $this->productSellingDeniedRecalculator->calculateSellingDeniedForProduct($product);

        return $product;
    }

    /**
     * @param string $productCatnum
     * @return \App\Model\Product\Product|null
     */
    public function findOneByCatnumExcludeMainVariants($productCatnum): ?Product
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
        $this->generateOldEshopUrlForProduct($product, $productData);
        $this->friendlyUrlFacade->saveUrlListFormData('front_product_detail', $product->getId(), $productData->urls);
        $this->storeUrls($product);

        $this->pluginCrudExtensionFacade->saveAllData('product', $product->getId(), $productData->pluginData);

        $this->productAvailabilityRecalculationScheduler->scheduleProductForImmediateRecalculation($product);
        $this->productVisibilityFacade->refreshProductsVisibilityForMarkedDelayed();
        $this->productPriceRecalculationScheduler->scheduleProductForImmediateRecalculation($product);

        $productToExport = $product->isVariant() ? $product->getMainVariant() : $product;
        $this->productExportScheduler->scheduleRowIdForImmediateExport($productToExport->getId());

        foreach ($productData->stockProductData as $productStockData) {
            $stock = $this->stockFacade->getById($productStockData->stockId);
            $this->productStockFacade->editProductStockRelation($product, $stock, $productStockData);
        }

        $this->productSellingDeniedRecalculator->calculateSellingDeniedForProduct($product);

        return $product;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Product\ProductData $productData
     */
    private function generateOldEshopUrlForProduct(Product $product, ProductData $productData): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $path = 'article/' . $product->getCatnum();
            $friendlyUrl = $this->friendlyUrlRepository->findByDomainIdAndSlug($domainConfig->getId(), $path);

            if ($friendlyUrl === null) {
                $productData->urls->newUrls[] = [
                    UrlListData::FIELD_DOMAIN => $domainConfig->getId(),
                    UrlListData::FIELD_SLUG => $path,
                ];
            }
        }
    }

    /**
     * @param int $productId
     */
    public function delete($productId): void
    {
        $product = $this->productRepository->getById($productId);
        if ($product->isVariant()) {
            if ($product->getMainVariant()->getDefaultVariant() === $product) {
                throw new DeleteDefaultVariantException($product);
            }
        }

        parent::delete($productId);
    }

    /**
     * @param \App\Model\Product\Product $product
     */
    private function storeUrls(Product $product): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            if ($product->getName($domainConfig->getLocale()) !== null) {
                $productUriName = $product->getFullname($domainConfig->getLocale()) . '-' . $product->getCatnum();

                $this->friendlyUrlFacade->createFriendlyUrlForDomain(
                    'front_product_detail',
                    $product->getId(),
                    $productUriName,
                    $domainConfig->getId(),
                    [self::PRODUCT_PATH_PREFIX]
                );
            }
        }
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Product\ProductFilesData $productFilesData
     */
    public function editProductFileAttributes(Product $product, ProductFilesData $productFilesData): void
    {
        $product->editFileAttributes($productFilesData);
        $this->em->flush();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Product\ProductData $productData
     */
    public function setAdditionalDataAfterCreate(Product $product, ProductData $productData)
    {
        // Persist of ProductCategoryDomain requires known primary key of Product
        // @see https://github.com/doctrine/doctrine2/issues/4869
        $productCategoryDomains = $this->productCategoryDomainFactory->createMultiple($product, $productData->categoriesByDomainId);
        $product->setProductCategoryDomains($productCategoryDomains);
        $this->em->flush();

        $this->saveParameters($product, $productData->parameters);
        $this->createProductVisibilities($product);
        $this->refreshProductAccessories($product, $productData->accessories);
        $this->imageFacade->manageImages($product, $productData->images);
        $this->productHiddenRecalculator->calculateHiddenForProduct($product);

        $this->generateOldEshopUrlForProduct($product, $productData);
        $this->friendlyUrlFacade->saveUrlListFormData('front_product_detail', $product->getId(), $productData->urls);
        $this->storeUrls($product);

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
    public function getProductTransferredFileUrl(string $fileName, string $domainUrl, ?string $browserCacheCleanerSuffix = null): string
    {
        return $domainUrl . $this->productFilesUrlPrefix . $fileName . ($browserCacheCleanerSuffix !== null ? '?' . md5($browserCacheCleanerSuffix) : '');
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Component\Domain\Domain $domain
     * @return array
     */
    public function getDownloadFilesForProductByDomain(Product $product, Domain $domain): array
    {
        $downloadFileUrls = [];
        if ($product->isDownloadAssemblyInstructionFiles() === false && $product->getAssemblyInstructionCode($domain->getId()) !== null) {
            $url = $this->getProductTransferredFileUrl(
                $product->getProductFileNameByType(
                    $domain->getId(),
                    \App\Model\Product\Product::FILE_IDENTIFICATOR_ASSEMBLY_INSTRUCTION_TYPE
                ),
                $domain->getUrl(),
                $product->getAssemblyInstructionCode($domain->getId())
            );
            $downloadFileUrls[] = [
                'anchor_text' => t('Instalační manuál'),
                'url' => $url,
            ];
        }

        if ($product->isDownloadProductTypePlanFiles() === false && $product->getProductTypePlanCode($domain->getId()) !== null) {
            $url = $this->getProductTransferredFileUrl(
                $product->getProductFileNameByType(
                    $domain->getId(),
                    \App\Model\Product\Product::FILE_IDENTIFICATOR_PRODUCT_TYPE_PLAN_TYPE
                ),
                $domain->getUrl(),
                $product->getProductTypePlanCode($domain->getId())
            );
            $downloadFileUrls[] = [
                'anchor_text' => t('Typový plán'),
                'url' => $url,
            ];
        }

        return $downloadFileUrls;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return string
     */
    public function getAssemblyInstructionFilename(Product $product, int $domainId): string
    {
        return $product->getAssemblyInstructionCode($domainId) . self::ASSETS_FILE_TYPE;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return string
     */
    public function getProductTypePlanFilename(Product $product, int $domainId): string
    {
        return $product->getProductTypePlanCode($domainId) . self::ASSETS_FILE_TYPE;
    }

    /**
     * @param \App\Model\Product\Product $product
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
            /** @var \App\Model\Product\Parameter\ParameterValueData $parameterValueData */
            $parameterValueData = $productParameterValueData->parameterValueData;
            $parameterValue = $this->parameterRepository->findOrCreateParameterValueByParameterValueData(
                $parameterValueData
            );

            $productParameterValue = $this->productParameterValueFactory->create(
                $product,
                $productParameterValueData->parameter,
                $parameterValue
            );
            $this->em->persist($productParameterValue);
            $toFlush[] = $productParameterValue;
        }

        if (count($toFlush) > 0) {
            $this->em->flush($toFlush);
        }
    }

    /**
     * @param string $catnum
     * @return \App\Model\Product\Product|null
     */
    public function findMainVariantByCatnum(string $catnum): ?Product
    {
        return $this->productRepository->findMainVariantByCatnum($catnum);
    }

    /**
     * @param string $catnum
     * @return \App\Model\Product\Product|null
     */
    public function findByCatnum(string $catnum): ?Product
    {
        return $this->productRepository->findByCatnum($catnum);
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @return \App\Model\Product\Product
     */
    public function createProductAsMainVariant(ProductData $productData): Product
    {
        $product = $this->create($productData);
        $product->setAsMainVariant();
        $this->em->flush();

        return $product;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Product\Product[] $accessories
     */
    public function refreshProductAccessories(Product $product, array $accessories): void
    {
        parent::refreshProductAccessories($product, $accessories);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Product\Product $variant
     */
    public function setDefaultVariant(Product $product, Product $variant): void
    {
        $product->setDefaultVariant($variant);
        $this->em->flush();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getNonSellingPriceByProductAndDomainId(Product $product, int $domainId): ?Money
    {
        if ($product->isMainVariant()) {
            $variants = $this->productRepository->getAllSellableVariantsByMainVariant(
                $product,
                $domainId,
                null
            );

            /** @var \App\Model\Product\Product[] $variants */
            if (count($variants) === 0) {
                return null;
            }

            $nonSellingVariantMoney = null;
            $sellingVariantMoney = null;
            foreach ($variants as $variant) {
                $variantSellingPriceWithVat = $variant->getSellingPriceWithVat($domainId);
                $variantHighPriceWithVat = $variant->getHighPriceWithVat($domainId);
                if ($variantHighPriceWithVat === null) {
                    return null;
                }
                /** @var \Shopsys\FrameworkBundle\Component\Money\Money $variantHighPriceWithVat */
                if ($nonSellingVariantMoney === null) {
                    $sellingVariantMoney = $variantSellingPriceWithVat;
                    $nonSellingVariantMoney = $variantHighPriceWithVat;
                    continue;
                }

                if ($variantSellingPriceWithVat->compare($sellingVariantMoney) !== 0) {
                    return null;
                }
                if ($variantHighPriceWithVat->compare($nonSellingVariantMoney) !== 0) {
                    return null;
                }
            }

            return $nonSellingVariantMoney;
        }

        return $product->getHighPriceWithVat($domainId);
    }
}
