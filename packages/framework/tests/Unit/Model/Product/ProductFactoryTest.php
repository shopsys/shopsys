<?php

namespace Tests\FrameworkBundle\Unit\Model\Product;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityCalculation;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFactory;

class ProductFactoryTest extends TestCase
{
    protected ProductFactory $productFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->productFactory = new ProductFactory(
            new EntityNameResolver([]),
            $this->getProductAvailabilityCalculationMock()
        );

        parent::setUp();
    }

    public function testCreateVariant()
    {
        $mainProduct = Product::create(TestProductProvider::getTestProductData());
        $variants = [];

        $mainVariant = $this->productFactory->createMainVariant(TestProductProvider::getTestProductData(), $mainProduct, $variants);

        $this->assertNotSame($mainProduct, $mainVariant);
        $this->assertTrue(in_array($mainProduct, $mainVariant->getVariants(), true));
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityCalculation|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getProductAvailabilityCalculationMock()
    {
        $dummyAvailability = new Availability(new AvailabilityData());
        $productAvailabilityCalculationMock = $this->getMockBuilder(ProductAvailabilityCalculation::class)
            ->disableOriginalConstructor()
            ->setMethods(['calculateAvailability'])
            ->getMock();
        $productAvailabilityCalculationMock
            ->expects($this->any())
            ->method('calculateAvailability')
            ->willReturn($dummyAvailability);

        return $productAvailabilityCalculationMock;
    }
}
