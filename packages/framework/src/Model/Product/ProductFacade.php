<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFactory;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueFactory;
use Shopsys\FrameworkBundle\Model\Product\Pricing\Exception\MainVariantPriceCalculationException;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum;
use Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoFacade;
use Shopsys\FrameworkBundle\Model\Stock\ProductStockData;
use Shopsys\FrameworkBundle\Model\Stock\ProductStockFacade;
use Shopsys\FrameworkBundle\Model\Stock\StockFacade;

class ProductFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ProductRepository $productRepository,
        protected readonly ParameterRepository $parameterRepository,
        protected readonly Domain $domain,
        protected readonly ImageFacade $imageFacade,
        protected readonly PricingGroupRepository $pricingGroupRepository,
        protected readonly ProductManualInputPriceFacade $productManualInputPriceFacade,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ProductAccessoryRepository $productAccessoryRepository,
        protected readonly PluginCrudExtensionFacade $pluginCrudExtensionFacade,
        protected readonly ProductFactory $productFactory,
        protected readonly ProductAccessoryFactory $productAccessoryFactory,
        protected readonly ProductCategoryDomainFactory $productCategoryDomainFactory,
        protected readonly ProductParameterValueFactory $productParameterValueFactory,
        protected readonly ProductVisibilityFactory $productVisibilityFactory,
        protected readonly ProductPriceCalculation $productPriceCalculation,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        protected readonly ProductStockFacade $productStockFacade,
        protected readonly StockFacade $stockFacade,
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly ProductVideoFacade $productVideoFacade,
        protected readonly ProductPromotionXyFactory $productPromotionXyFactory,
        protected readonly ProductPromotionXyDataFactory $productPromotionXyDataFactory,
        protected readonly ProductPromotionXyRepository $productPromotionXyRepository,
    ) {
    }

    public function getById(int $productId): Product
    {
        return $this->productRepository->getById($productId);
    }

    public function create(
        ProductData $productData,
        string $priority = ProductRecalculationPriorityEnum::REGULAR,
    ): Product {
        $product = $this->productFactory->create($productData);

        $this->em->persist($product);
        $this->refreshProductPromotions($product, $productData);
        $this->em->flush();
        $this->setAdditionalDataAfterCreate($product, $productData);

        $this->pluginCrudExtensionFacade->saveAllData('product', $product->getId(), $productData->pluginData);

        $this->editProductStockRelation($productData, $product);

        $this->productRecalculationDispatcher->dispatchSingleProductId($product->getId(), $priority);

        return $product;
    }

    public function setAdditionalDataAfterCreate(Product $product, ProductData $productData): void
    {
        // Persist of ProductCategoryDomain requires known primary key of Product
        // @see https://github.com/doctrine/doctrine2/issues/4869
        $productCategoryDomains = $this->productCategoryDomainFactory->createMultiple(
            $product,
            $productData->categoriesByDomainId,
        );
        $product->setProductCategoryDomains($productCategoryDomains);
        $this->em->flush();

        $this->saveParameters($product, $productData->parameters);
        $this->createProductVisibilities($product);
        $this->productManualInputPriceFacade->refreshProductManualInputPrices($product, $productData->productInputPricesByDomain);
        $this->refreshProductAccessories($product, $productData->accessories);

        $this->imageFacade->manageImages($product, $productData->images);
        $this->uploadedFileFacade->manageFiles($product, $productData->files);

        $this->friendlyUrlFacade->saveUrlListFormData('front_product_detail', $product->getId(), $productData->urls);
        $this->friendlyUrlFacade->createFriendlyUrls('front_product_detail', $product->getId(), $product->getFullNames());

        $this->productVideoFacade->saveProductVideosToProduct($product, $productData->productVideosData);
    }

    public function edit(
        int $productId,
        ProductData $productData,
        string $priority = ProductRecalculationPriorityEnum::REGULAR,
    ): Product {
        $product = $this->productRepository->getById($productId);
        $originalNames = $product->getFullNames();

        $productCategoryDomains = $this->productCategoryDomainFactory->createMultiple(
            $product,
            $productData->categoriesByDomainId,
        );
        $product->edit($productCategoryDomains, $productData);

        $this->refreshProductPromotions($product, $productData);

        $this->saveParameters($product, $productData->parameters);

        if (!$product->isMainVariant()) {
            $this->productManualInputPriceFacade->refreshProductManualInputPrices($product, $productData->productInputPricesByDomain);
        }

        if ($product->isMainVariant()) {
            $removedVariantIds = $product->refreshVariants($productData->variants);
            $this->productRecalculationDispatcher->dispatchProductIds($removedVariantIds, $priority);
        }

        $this->refreshProductAccessories($product, $productData->accessories);
        $this->em->flush();

        $this->imageFacade->manageImages($product, $productData->images);
        $this->uploadedFileFacade->manageFiles($product, $productData->files);

        $this->friendlyUrlFacade->saveUrlListFormData('front_product_detail', $product->getId(), $productData->urls);
        $this->createFriendlyUrlsWhenRenamed($product, $originalNames);

        $this->pluginCrudExtensionFacade->saveAllData('product', $product->getId(), $productData->pluginData);

        $this->editProductStockRelation($productData, $product);
        $this->productVideoFacade->saveProductVideosToProduct($product, $productData->productVideosData);

        $this->productRecalculationDispatcher->dispatchSingleProductId($product->getId(), $priority);

        return $product;
    }

    protected function refreshProductPromotions(Product $product, ProductData $productData): void
    {
        foreach ($productData->promotionXyData as $domainId => $promotionData) {
            if ($promotionData === null) {
                continue;
            }

            $this->refreshProductPromotionForDomain($promotionData, $product, $domainId);
        }
    }

    protected function refreshProductPromotionForDomain(
        ProductPromotionXyData $promotionData,
        Product $product,
        int $domainId,
    ): void {
        $promotionBuyQuantity = $promotionData->buyQuantity;
        $promotionFreeQuantity = $promotionData->freeQuantity;
        $currentPromotion = $product->getPromotionXy($domainId);

        if ($promotionBuyQuantity === $currentPromotion?->getBuyQuantity() && $promotionFreeQuantity === $currentPromotion?->getFreeQuantity()) {
            return;
        }

        if ($promotionBuyQuantity === null || $promotionFreeQuantity === null) {
            if ($currentPromotion !== null) {
                $product->setPromotionXy(null, $domainId);
            }

            return;
        }

        $productPromotionXyData = $this->productPromotionXyDataFactory->create();
        $productPromotionXyData->buyQuantity = $promotionBuyQuantity;
        $productPromotionXyData->freeQuantity = $promotionFreeQuantity;

        $promotion = $this->productPromotionXyRepository->findPromotionXyByQuantities($promotionBuyQuantity, $promotionFreeQuantity);

        if ($promotion === null) {
            $promotion = $this->productPromotionXyFactory->create($productPromotionXyData);
        }

        $this->em->persist($promotion);
        $product->setPromotionXy($promotion, $domainId);
        $this->em->flush();
    }

    public function delete(
        int $productId,
        string $priority = ProductRecalculationPriorityEnum::REGULAR,
    ): void {
        $product = $this->productRepository->getById($productId);

        if ($product->isMainVariant()) {
            foreach ($product->getVariants() as $variantProduct) {
                $variantProduct->unsetMainVariant();
            }
        }

        if ($product->isVariant() && $product->getMainVariant() !== null) {
            $this->productRecalculationDispatcher->dispatchSingleProductId($product->getMainVariant()->getId(), $priority);
        }

        $this->productRecalculationDispatcher->dispatchSingleProductId($product->getId(), $priority);

        $this->em->remove($product);
        $this->em->flush();

        $this->pluginCrudExtensionFacade->removeAllData('product', $product->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData[] $productParameterValuesData
     */
    protected function saveParameters(Product $product, array $productParameterValuesData): void
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
            $parameterValueData = $productParameterValueData->parameterValueData;
            $parameterValue = $this->parameterRepository->findOrCreateParameterValueByParameterValueData(
                $parameterValueData,
            );

            if ($productParameterValueData->parameter->isSlider()) {
                $parameterValue->setNumericValue($productParameterValueData->parameterValueData->numericValue);
                $toFlush[] = $parameterValue;
            }

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
     * @return iterable<array{id: int}>
     */
    public function iterateAllProductIdsExceptVariant(): iterable
    {
        return $this->productRepository->iterateAllProductIdsExceptVariant();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface[][]
     */
    public function getAllProductPricesIndexedByDomainId(Product $product): array
    {
        $productSellingPrices = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $productSellingPrices[$domainId] = $this->getAllProductPricesByDomainId($product, $domainId);
        }

        return $productSellingPrices;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface[]
     */
    public function getAllProductPricesByDomainId(Product $product, int $domainId): array
    {
        $productPrices = [];

        foreach ($this->pricingGroupRepository->getPricingGroupsByDomainId($domainId) as $pricingGroup) {
            $productPrices[$pricingGroup->getId()] = $this->getProductPriceForPricingGroup($product, $domainId, $pricingGroup);
        }

        return $productPrices;
    }

    public function getProductPriceForDefaultPricingGroup(Product $product, int $domainId): ProductPriceInterface
    {
        $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);

        return $this->getProductPriceForPricingGroup($product, $domainId, $pricingGroup);
    }

    protected function getProductPriceForPricingGroup(
        Product $product,
        int $domainId,
        PricingGroup $pricingGroup,
    ): ProductPriceInterface {
        try {
            $sellingPrice = $this->productPriceCalculation->calculatePrice($product, $domainId, $pricingGroup);
        } catch (MainVariantPriceCalculationException) {
            $sellingPrice = new ProductPrice(Price::zero(), $pricingGroup, false);
        }

        return $sellingPrice;
    }

    protected function createProductVisibilities(Product $product): void
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

        if (count($toFlush) > 0) {
            $this->em->flush();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $accessories
     */
    protected function refreshProductAccessories(Product $product, array $accessories): void
    {
        $oldProductAccessories = $this->productAccessoryRepository->getAllByProduct($product);

        foreach ($oldProductAccessories as $oldProductAccessory) {
            $this->em->remove($oldProductAccessory);
        }
        $this->em->flush();

        $toFlush = [];

        foreach ($accessories as $position => $accessory) {
            $newProductAccessory = $this->productAccessoryFactory->create($product, $accessory, $position);
            $this->em->persist($newProductAccessory);
            $toFlush[] = $newProductAccessory;
        }

        if (count($toFlush) > 0) {
            $this->em->flush();
        }
    }

    public function getOneByCatnumExcludeMainVariants(
        string $productCatnum,
    ): Product {
        return $this->productRepository->getOneByCatnumExcludeMainVariants($productCatnum);
    }

    public function getByUuid(string $uuid): Product
    {
        return $this->productRepository->getOneByUuid($uuid);
    }

    public function editProductStockRelation(ProductData $productData, Product $product): void
    {
        $stockIds = array_map(
            static fn (ProductStockData $productStockData): int => $productStockData->stockId,
            $productData->productStockData,
        );

        $stocksIndexedById = $this->stockFacade->getStocksByIdsIndexedById($stockIds);

        $this->productStockFacade->editProductStockRelations(
            $product,
            $stocksIndexedById,
            $productData->productStockData,
        );
    }

    /**
     * @param int[] $ids
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getAllByIds(array $ids): array
    {
        return $this->productRepository->getAllByIds($ids);
    }

    protected function createFriendlyUrlsWhenRenamed(Product $product, array $originalNames): void
    {
        $changedNames = $this->getChangedNamesByLocale($product, $originalNames);

        if (count($changedNames) === 0) {
            return;
        }

        $this->friendlyUrlFacade->createFriendlyUrls(
            'front_product_detail',
            $product->getId(),
            $changedNames,
        );
    }

    protected function getChangedNamesByLocale(Product $product, array $originalNames): array
    {
        $changedProductNames = [];

        foreach ($product->getFullNames() as $locale => $name) {
            if ($name !== null && $name !== $originalNames[$locale]) {
                $changedProductNames[$locale] = $name;
            }
        }

        return $changedProductNames;
    }

    public function findByCatnum(string $catnum): ?Product
    {
        return $this->productRepository->findByCatnum($catnum);
    }

    /**
     * @param string[] $catnums
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function findAllByCatnums(array $catnums): array
    {
        return $this->productRepository->findAllByCatnums($catnums);
    }

    public function getCalculatedSellingDeniedPerDomainIds(Product $product): array
    {
        return $this->productRepository->getCalculatedSellingDeniedPerDomainIds($product);
    }
}
