<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Product\Availability;

use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\ShopBundle\DataFixtures\Demo\ProductDataFixture;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

final class AvailabilityFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator
     */
    private $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityDataFactoryInterface
     * @inject
     */
    private $availabilityDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade
     * @inject
     */
    private $availabilityFacade;

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

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = $this->getEntityManager();
    }

    public function testDeleteByIdAndReplaceProductAvailability(): void
    {
        /** @var \Shopsys\ShopBundle\Model\Product\Product $product */
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
        /** @var \Shopsys\ShopBundle\Model\Product\Product $product */
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
