<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Availability;

use App\DataFixtures\Demo\AvailabilityDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculator;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class ProductAvailabilityRecalculatorTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductDataFactoryInterface $productDataFactory;

    /**
     * @inject
     */
    private ProductAvailabilityRecalculator $productAvailabilityRecalculator;

    /**
     * @inject
     */
    private AvailabilityFacade $availabilityFacade;

    public function testRecalculateOnProductEditNotUsingStock()
    {
        $productId = 1;

        $product = $this->productFacade->getById($productId);

        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->usingStock = false;
        $productData->availability = $this->getReference(AvailabilityDataFixture::AVAILABILITY_ON_REQUEST);

        $this->productFacade->edit($productId, $productData);
        $this->productAvailabilityRecalculator->runAllScheduledRecalculations();
        $this->em->flush();
        $this->em->clear();

        $productFromDb = $this->productFacade->getById($productId);

        $this->assertSame(
            $this->getReference(AvailabilityDataFixture::AVAILABILITY_ON_REQUEST),
            $productFromDb->getCalculatedAvailability()
        );
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
        $this->em->flush();
        $this->em->clear();

        $productFromDb = $this->productFacade->getById($productId);

        $this->assertSame(
            $this->availabilityFacade->getDefaultInStockAvailability(),
            $productFromDb->getCalculatedAvailability()
        );
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
        $this->em->flush();
        $this->em->clear();

        $productFromDb = $this->productFacade->getById($productId);

        $this->assertSame(
            $this->getReference(AvailabilityDataFixture::AVAILABILITY_OUT_OF_STOCK),
            $productFromDb->getCalculatedAvailability()
        );
    }
}
