<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Product;

use ReflectionClass;
use Shopsys\ShopBundle\DataFixtures\Demo\AvailabilityDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\ProductDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\UnitDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\VatDataFixture;
use Shopsys\ShopBundle\Model\Product\Product;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class ProductFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface
     * @inject
     */
    private $productDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     * @inject
     */
    private $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler
     * @inject
     */
    private $productPriceRecalculationScheduler;

    /**
     * @dataProvider getTestHandleOutOfStockStateDataProvider
     * @param mixed $hidden
     * @param mixed $sellingDenied
     * @param mixed $stockQuantity
     * @param mixed $outOfStockAction
     * @param mixed $calculatedHidden
     * @param mixed $calculatedSellingDenied
     */
    public function testHandleOutOfStockState(
        $hidden,
        $sellingDenied,
        $stockQuantity,
        $outOfStockAction,
        $calculatedHidden,
        $calculatedSellingDenied
    ) {
        $productData = $this->productDataFactory->create();
        $productData->hidden = $hidden;
        $productData->sellingDenied = $sellingDenied;
        $productData->stockQuantity = $stockQuantity;
        $productData->outOfStockAction = $outOfStockAction;
        $productData->usingStock = true;
        $productData->availability = $this->getReference(AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $productData->outOfStockAvailability = $this->getReference(AvailabilityDataFixture::AVAILABILITY_OUT_OF_STOCK);
        $productData->vat = $this->getReference(VatDataFixture::VAT_HIGH);
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES);

        $product = $this->productFacade->create($productData);

        $this->getEntityManager()->clear();

        $productFromDb = $this->productFacade->getById($product->getId());

        $this->assertSame($productFromDb->getCalculatedHidden(), $calculatedHidden);
        $this->assertSame($calculatedSellingDenied, $productFromDb->getCalculatedSellingDenied());
    }

    public function getTestHandleOutOfStockStateDataProvider()
    {
        return [
            [
                'hidden' => true,
                'sellingDenied' => true,
                'stockQuantity' => 0,
                'outOfStockAction' => Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY,
                'calculatedHidden' => true,
                'calculatedSellingDenied' => true,
            ],
            [
                'hidden' => false,
                'sellingDenied' => false,
                'stockQuantity' => 0,
                'outOfStockAction' => Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY,
                'calculatedHidden' => false,
                'calculatedSellingDenied' => false,
            ],
            [
                'hidden' => true,
                'sellingDenied' => false,
                'stockQuantity' => 0,
                'outOfStockAction' => Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY,
                'calculatedHidden' => true,
                'calculatedSellingDenied' => false,
            ],
            [
                'hidden' => false,
                'sellingDenied' => true,
                'stockQuantity' => 0,
                'outOfStockAction' => Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY,
                'calculatedHidden' => false,
                'calculatedSellingDenied' => true,
            ],
            [
                'hidden' => false,
                'sellingDenied' => false,
                'stockQuantity' => 0,
                'outOfStockAction' => Product::OUT_OF_STOCK_ACTION_EXCLUDE_FROM_SALE,
                'calculatedHidden' => false,
                'calculatedSellingDenied' => true,
            ],
            [
                'hidden' => false,
                'sellingDenied' => false,
                'stockQuantity' => 0,
                'outOfStockAction' => Product::OUT_OF_STOCK_ACTION_HIDE,
                'calculatedHidden' => true,
                'calculatedSellingDenied' => false,
            ],
        ];
    }

    public function testEditMarkProductForVisibilityRecalculation()
    {
        /** @var \Shopsys\ShopBundle\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        $reflectionClass = new ReflectionClass(Product::class);
        $reflectionPropertyRecalculateVisibility = $reflectionClass->getProperty('recalculateVisibility');
        $reflectionPropertyRecalculateVisibility->setAccessible(true);
        $reflectionPropertyRecalculateVisibility->setValue($product, false);

        $this->productFacade->edit($product->getId(), $this->productDataFactory->createFromProduct($product));

        $this->assertSame(true, $reflectionPropertyRecalculateVisibility->getValue($product));
    }

    public function testEditSchedulesPriceRecalculation()
    {
        /** @var \Shopsys\ShopBundle\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $productId = $product->getId();

        $this->productFacade->edit($productId, $this->productDataFactory->create());

        $this->assertArrayHasKey($productId, $this->productPriceRecalculationScheduler->getProductsForImmediateRecalculation());
    }
}
