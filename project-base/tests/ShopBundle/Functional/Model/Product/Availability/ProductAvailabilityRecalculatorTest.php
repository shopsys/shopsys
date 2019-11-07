<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Product\Availability;

use Shopsys\ShopBundle\DataFixtures\Demo\AvailabilityDataFixture;
use Shopsys\ShopBundle\Model\Product\Product;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class ProductAvailabilityRecalculatorTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     * @inject
     */
    private $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface
     * @inject
     */
    private $productDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculator
     * @inject
     */
    private $productAvailabilityRecalculator;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade
     * @inject
     */
    private $availabilityFacade;

    public function testRecalculateOnProductEditNotUsingStock()
    {
        $productId = 1;

        $product = $this->productFacade->getById($productId);

        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->usingStock = false;
        $productData->availability = $this->getReference(AvailabilityDataFixture::AVAILABILITY_ON_REQUEST);

        $this->productFacade->edit($productId, $productData);
        $this->productAvailabilityRecalculator->runAllScheduledRecalculations();
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $productFromDb = $this->productFacade->getById($productId);

        $this->assertSame($this->getReference(AvailabilityDataFixture::AVAILABILITY_ON_REQUEST), $productFromDb->getCalculatedAvailability());
    }

    public function testRecalculateOnProductEditUsingStockInStock()
    {
        $productId = 1;

        $product = $this->productFacade->getById($productId);

        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->usingStock = true;
        $productData->stockQuantity = 5;
        $productData->outOfStockAvailability = $this->getReference(AvailabilityDataFixture::AVAILABILITY_OUT_OF_STOCK);
        $productData->availability = $this->getReference(AvailabilityDataFixture::AVAILABILITY_ON_REQUEST);

        $this->productFacade->edit($productId, $productData);
        $this->productAvailabilityRecalculator->runAllScheduledRecalculations();
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $productFromDb = $this->productFacade->getById($productId);

        $this->assertSame($this->availabilityFacade->getDefaultInStockAvailability(), $productFromDb->getCalculatedAvailability());
    }

    public function testRecalculateOnProductEditUsingStockOutOfStock()
    {
        $productId = 1;

        $product = $this->productFacade->getById($productId);

        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->usingStock = true;
        $productData->stockQuantity = 0;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY;
        $productData->outOfStockAvailability = $this->getReference(AvailabilityDataFixture::AVAILABILITY_OUT_OF_STOCK);
        $productData->availability = $this->getReference(AvailabilityDataFixture::AVAILABILITY_ON_REQUEST);

        $this->productFacade->edit($productId, $productData);
        $this->productAvailabilityRecalculator->runAllScheduledRecalculations();
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $productFromDb = $this->productFacade->getById($productId);

        $this->assertSame($this->getReference(AvailabilityDataFixture::AVAILABILITY_OUT_OF_STOCK), $productFromDb->getCalculatedAvailability());
    }
}
