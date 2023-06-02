<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Availability;

use App\DataFixtures\Demo\ProductDataFixture;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

final class AvailabilityFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private AvailabilityDataFactoryInterface $availabilityDataFactory;

    /**
     * @inject
     */
    private AvailabilityFacade $availabilityFacade;

    /**
     * @inject
     */
    private ProductDataFactoryInterface $productDataFactory;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    public function testDeleteByIdAndReplaceProductAvailability(): void
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        $productData = $this->productDataFactory->createFromProduct($product);

        $availabilityToDelete = $this->createNewAvailability();
        $productData->usingStock = false;
        $productData->availability = $availabilityToDelete;

        $this->productFacade->edit($product->getId(), $productData);

        $availabilityToReplaceWith = $this->createNewAvailability();
        $this->availabilityFacade->deleteById($availabilityToDelete->getId(), $availabilityToReplaceWith->getId());

        $this->em->refresh($product);

        $this->assertSame($availabilityToReplaceWith, $product->getAvailability());
    }

    public function testDeleteByIdAndReplaceProductOutOfStockAvailability(): void
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        $productData = $this->productDataFactory->createFromProduct($product);

        $availabilityToDelete = $this->createNewAvailability();
        $productData->usingStock = true;
        $productData->stockQuantity = 1;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY;
        $productData->outOfStockAvailability = $availabilityToDelete;

        $this->productFacade->edit($product->getId(), $productData);

        $availabilityToReplaceWith = $this->createNewAvailability();
        $this->availabilityFacade->deleteById($availabilityToDelete->getId(), $availabilityToReplaceWith->getId());

        $this->em->refresh($product);

        $this->assertSame($availabilityToReplaceWith, $product->getOutOfStockAvailability());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    private function createNewAvailability(): Availability
    {
        $availabilityData = $this->availabilityDataFactory->create();

        foreach (array_keys($availabilityData->name) as $locale) {
            $availabilityData->name[$locale] = 'new availability';
        }

        return $this->availabilityFacade->create($availabilityData);
    }
}
