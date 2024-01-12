<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Pricing\Exception\MainVariantPriceCalculationException;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductSellingPrice;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnumInterface;
use Shopsys\FrameworkBundle\Model\Stock\ProductStockData;
use Shopsys\FrameworkBundle\Model\Stock\ProductStockFacade;
use Shopsys\FrameworkBundle\Model\Stock\StockFacade;

class ProductFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository $pricingGroupRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade $productManualInputPriceFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginCrudExtensionFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFactoryInterface $productFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFactoryInterface $productAccessoryFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomainFactoryInterface $productCategoryDomainFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueFactoryInterface $productParameterValueFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFactoryInterface $productVisibilityFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation $productPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher
     * @param \Shopsys\FrameworkBundle\Model\Stock\ProductStockFacade $productStockFacade
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockFacade $stockFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ProductRepository $productRepository,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly ParameterRepository $parameterRepository,
        protected readonly Domain $domain,
        protected readonly ImageFacade $imageFacade,
        protected readonly PricingGroupRepository $pricingGroupRepository,
        protected readonly ProductManualInputPriceFacade $productManualInputPriceFacade,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ProductAccessoryRepository $productAccessoryRepository,
        protected readonly PluginCrudExtensionFacade $pluginCrudExtensionFacade,
        protected readonly ProductFactoryInterface $productFactory,
        protected readonly ProductAccessoryFactoryInterface $productAccessoryFactory,
        protected readonly ProductCategoryDomainFactoryInterface $productCategoryDomainFactory,
        protected readonly ProductParameterValueFactoryInterface $productParameterValueFactory,
        protected readonly ProductVisibilityFactoryInterface $productVisibilityFactory,
        protected readonly ProductPriceCalculation $productPriceCalculation,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        protected readonly ProductStockFacade $productStockFacade,
        protected readonly StockFacade $stockFacade,
    ) {
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
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $productData
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum|null $priority nullable because of https://github.com/nikic/PHP-Parser/pull/940
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function create(
        ProductData $productData,
        ?ProductRecalculationPriorityEnumInterface $priority = null,
    ): Product {
        $product = $this->productFactory->create($productData);

        $this->em->persist($product);
        $this->em->flush();
        $this->setAdditionalDataAfterCreate($product, $productData);

        $this->pluginCrudExtensionFacade->saveAllData('product', $product->getId(), $productData->pluginData);

        $this->editProductStockRelation($productData, $product);

        $this->productRecalculationDispatcher->dispatchSingleProductId($product->getId(), $priority ?? ProductRecalculationPriorityEnum::REGULAR);

        return $product;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $productData
     */
    public function setAdditionalDataAfterCreate(Product $product, ProductData $productData)
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
        $this->productManualInputPriceFacade->refreshProductManualInputPrices($product, $productData->manualInputPricesByPricingGroupId);
        $this->refreshProductAccessories($product, $productData->accessories);

        $this->imageFacade->manageImages($product, $productData->images);
        $this->friendlyUrlFacade->saveUrlListFormData('front_product_detail', $product->getId(), $productData->urls);
        $this->friendlyUrlFacade->createFriendlyUrls('front_product_detail', $product->getId(), $product->getNames());
    }

    /**
     * @param int $productId
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $productData
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum|null $priority nullable because of https://github.com/nikic/PHP-Parser/pull/940
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function edit(
        int $productId,
        ProductData $productData,
        ?ProductRecalculationPriorityEnumInterface $priority = null,
    ): Product {
        $product = $this->productRepository->getById($productId);

        $productCategoryDomains = $this->productCategoryDomainFactory->createMultiple(
            $product,
            $productData->categoriesByDomainId,
        );
        $product->edit($productCategoryDomains, $productData);

        $this->saveParameters($product, $productData->parameters);

        if (!$product->isMainVariant()) {
            $this->productManualInputPriceFacade->refreshProductManualInputPrices($product, $productData->manualInputPricesByPricingGroupId);
        }

        if ($product->isMainVariant()) {
            $product->refreshVariants($productData->variants);
        }

        $this->refreshProductAccessories($product, $productData->accessories);
        $this->em->flush();

        $this->imageFacade->manageImages($product, $productData->images);
        $this->friendlyUrlFacade->saveUrlListFormData('front_product_detail', $product->getId(), $productData->urls);
        $this->friendlyUrlFacade->createFriendlyUrls('front_product_detail', $product->getId(), $product->getFullnames());

        $this->pluginCrudExtensionFacade->saveAllData('product', $product->getId(), $productData->pluginData);

        $this->editProductStockRelation($productData, $product);

        $this->productRecalculationDispatcher->dispatchSingleProductId($product->getId(), $priority ?? ProductRecalculationPriorityEnum::REGULAR);

        return $product;
    }

    /**
     * @param int $productId
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum|null $priority nullable because of https://github.com/nikic/PHP-Parser/pull/940
     */
    public function delete(
        int $productId,
        ?ProductRecalculationPriorityEnumInterface $priority = null,
    ): void {
        $product = $this->productRepository->getById($productId);
        $productDeleteResult = $product->getProductDeleteResult();
        $productsForRecalculations = $productDeleteResult->getProductsForRecalculations();

        foreach ($productsForRecalculations as $productForRecalculations) {
            $this->productRecalculationDispatcher->dispatchSingleProductId($productForRecalculations->getId(), $priority ?? ProductRecalculationPriorityEnum::REGULAR);
        }

        $this->productRecalculationDispatcher->dispatchSingleProductId($product->getId(), $priority ?? ProductRecalculationPriorityEnum::REGULAR);

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
        $this->em->flush();

        $toFlush = [];

        foreach ($productParameterValuesData as $productParameterValueData) {
            $productParameterValue = $this->productParameterValueFactory->create(
                $product,
                $productParameterValueData->parameter,
                $this->parameterRepository->findOrCreateParameterValueByValueTextAndLocale(
                    $productParameterValueData->parameterValueData->text,
                    $productParameterValueData->parameterValueData->locale,
                ),
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
    public function iterateAllProductIds(): iterable
    {
        return $this->productRepository->iterateAllProductIds();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductSellingPrice[][]
     */
    public function getAllProductSellingPricesIndexedByDomainId(Product $product)
    {
        $productSellingPrices = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $productSellingPrices[$domainId] = $this->getAllProductSellingPricesByDomainId($product, $domainId);
        }

        return $productSellingPrices;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductSellingPrice[]
     */
    public function getAllProductSellingPricesByDomainId(Product $product, int $domainId): array
    {
        $productSellingPrices = [];

        foreach ($this->pricingGroupRepository->getPricingGroupsByDomainId($domainId) as $pricingGroup) {
            try {
                $sellingPrice = $this->productPriceCalculation->calculatePrice($product, $domainId, $pricingGroup);
            } catch (MainVariantPriceCalculationException $e) {
                $sellingPrice = new ProductPrice(Price::zero(), false);
            }
            $productSellingPrices[$pricingGroup->getId()] = new ProductSellingPrice($pricingGroup, $sellingPrice);
        }

        return $productSellingPrices;
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

        if (count($toFlush) > 0) {
            $this->em->flush();
        }
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

    /**
     * @param string $productCatnum
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getOneByCatnumExcludeMainVariants($productCatnum)
    {
        return $this->productRepository->getOneByCatnumExcludeMainVariants($productCatnum);
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getByUuid(string $uuid): Product
    {
        return $this->productRepository->getOneByUuid($uuid);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $productData
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     */
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
}
