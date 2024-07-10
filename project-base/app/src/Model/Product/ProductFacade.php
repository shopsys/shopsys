<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\Model\ProductVideo\ProductVideoFacade;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomainFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade as BaseProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum;
use Shopsys\FrameworkBundle\Model\Stock\ProductStockFacade;
use Shopsys\FrameworkBundle\Model\Stock\StockFacade;

/**
 * @property \App\Model\Product\ProductRepository $productRepository
 * @property \App\Model\Product\Parameter\ParameterRepository $parameterRepository
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @property \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
 * @property \App\Model\Product\ProductSellingDeniedRecalculator $productSellingDeniedRecalculator
 * @property \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation $productPriceCalculation
 * @method \App\Model\Product\Product getById(int $productId)
 * @method \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductSellingPrice[][] getAllProductSellingPricesIndexedByDomainId(\App\Model\Product\Product $product)
 * @method \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductSellingPrice[] getAllProductSellingPricesByDomainId(\App\Model\Product\Product $product, int $domainId)
 * @method refreshProductManualInputPrices(\App\Model\Product\Product $product, \Shopsys\FrameworkBundle\Component\Money\Money[]|null[] $manualInputPrices)
 * @method createProductVisibilities(\App\Model\Product\Product $product)
 * @method \App\Model\Product\Product getOneByCatnumExcludeMainVariants(string $productCatnum)
 * @method \App\Model\Product\Product getByUuid(string $uuid)
 * @method \App\Model\Product\Product create(\App\Model\Product\ProductData $productData, string $priority = \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum::REGULAR)
 * @property \App\Model\Product\ProductFactory $productFactory
 * @method setAdditionalDataAfterCreate(\App\Model\Product\Product $product, \App\Model\Product\ProductData $productData)
 * @method editProductStockRelation(\App\Model\Product\ProductData $productData, \App\Model\Product\Product $product)
 */
class ProductFacade extends BaseProductFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $em
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository $pricingGroupRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade $productManualInputPriceFacade
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginCrudExtensionFacade
     * @param \App\Model\Product\ProductFactory $productFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFactory $productAccessoryFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomainFactory $productCategoryDomainFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueFactory $productParameterValueFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFactory $productVisibilityFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation $productPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher
     * @param \Shopsys\FrameworkBundle\Model\Stock\ProductStockFacade $productStockFacade
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockFacade $stockFacade
     * @param \App\Model\ProductVideo\ProductVideoFacade $productVideoFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        ProductRepository $productRepository,
        ProductVisibilityFacade $productVisibilityFacade,
        ParameterRepository $parameterRepository,
        Domain $domain,
        ImageFacade $imageFacade,
        PricingGroupRepository $pricingGroupRepository,
        ProductManualInputPriceFacade $productManualInputPriceFacade,
        FriendlyUrlFacade $friendlyUrlFacade,
        ProductAccessoryRepository $productAccessoryRepository,
        PluginCrudExtensionFacade $pluginCrudExtensionFacade,
        ProductFactoryInterface $productFactory,
        ProductAccessoryFactoryInterface $productAccessoryFactory,
        ProductCategoryDomainFactoryInterface $productCategoryDomainFactory,
        ProductParameterValueFactoryInterface $productParameterValueFactory,
        ProductVisibilityFactoryInterface $productVisibilityFactory,
        ProductPriceCalculation $productPriceCalculation,
        ProductRecalculationDispatcher $productRecalculationDispatcher,
        ProductStockFacade $productStockFacade,
        StockFacade $stockFacade,
        private readonly ProductVideoFacade $productVideoFacade,
    ) {
        parent::__construct(
            $em,
            $productRepository,
            $productVisibilityFacade,
            $parameterRepository,
            $domain,
            $imageFacade,
            $pricingGroupRepository,
            $productManualInputPriceFacade,
            $friendlyUrlFacade,
            $productAccessoryRepository,
            $pluginCrudExtensionFacade,
            $productFactory,
            $productAccessoryFactory,
            $productCategoryDomainFactory,
            $productParameterValueFactory,
            $productVisibilityFactory,
            $productPriceCalculation,
            $productRecalculationDispatcher,
            $productStockFacade,
            $stockFacade,
        );
    }

    /**
     * @param string $productCatnum
     * @return \App\Model\Product\Product|null
     */
    public function findOneByCatnumExcludeMainVariants($productCatnum): ?BaseProduct
    {
        try {
            return $this->productRepository->getOneByCatnumExcludeMainVariants($productCatnum);
        } catch (ProductNotFoundException $exception) {
            return null;
        }
    }

    /**
     * @param int $productId
     * @param \App\Model\Product\ProductData $productData
     * @param string $priority
     * @return \App\Model\Product\Product
     */
    public function edit(
        int $productId,
        ProductData $productData,
        string $priority = ProductRecalculationPriorityEnum::REGULAR,
    ): Product {
        /** @var \App\Model\Product\Product $product */
        $product = parent::edit($productId, $productData, $priority);

        $this->productVideoFacade->saveProductVideosToProduct($product, $productData->productVideosData);

        return $product;
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
}
