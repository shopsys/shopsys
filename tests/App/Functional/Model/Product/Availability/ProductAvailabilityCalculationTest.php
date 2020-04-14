<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Availability;

use App\DataFixtures\Demo\AvailabilityDataFixture;
use App\DataFixtures\Demo\ProductTypeDataFixture;
use App\Model\Product\Availability\ProductAvailabilityCalculation;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use App\Model\Product\ProductRepository;
use Doctrine\ORM\EntityManager;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Tests\App\Test\FunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class ProductAvailabilityCalculationTest extends FunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \App\Model\Product\ProductDataFactory
     * @inject
     */
    private $productDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     * @inject
     */
    private $vatFacade;

    /**
     * @dataProvider getTestCalculateAvailabilityData
     * @param mixed $usingStock
     * @param mixed $stockQuantity
     * @param mixed $outOfStockAction
     * @param string|null $availability
     * @param string|null $outOfStockAvailability
     * @param string|null $defaultInStockAvailability
     * @param string|null $expectedCalculatedAvailability
     */
    public function testCalculateAvailability(
        $usingStock,
        $stockQuantity,
        $outOfStockAction,
        ?string $availability = null,
        ?string $outOfStockAvailability = null,
        ?string $defaultInStockAvailability = null,
        ?string $expectedCalculatedAvailability = null
    ) {
        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->create();
        $productData->usingStock = $usingStock;
        $productData->stockQuantity = $stockQuantity;

        if ($availability !== null) {
            $productData->availability = $this->getReference($availability);
        }

        $productData->outOfStockAction = $outOfStockAction;

        if ($outOfStockAvailability !== null) {
            $productData->outOfStockAvailability = $this->getReference($outOfStockAvailability);
        }
        $this->setProductTypes($productData);
        $this->setVats($productData);

        $product = Product::create($productData);

        $availabilityFacadeMock = $this->getMockBuilder(AvailabilityFacade::class)
            ->disableOriginalConstructor()
            ->getMock();
        $availabilityFacadeMock->expects($this->any())->method('getDefaultInStockAvailability')
            ->willReturn($this->getReference($defaultInStockAvailability));

        $productSellingDeniedRecalculatorMock = $this->createMock(ProductSellingDeniedRecalculator::class);
        $productVisibilityFacadeMock = $this->createMock(ProductVisibilityFacade::class);
        $entityManagerMock = $this->createMock(EntityManager::class);
        $productRepositoryMock = $this->createMock(ProductRepository::class);

        $productAvailabilityCalculation = new ProductAvailabilityCalculation(
            $availabilityFacadeMock,
            $productSellingDeniedRecalculatorMock,
            $productVisibilityFacadeMock,
            $entityManagerMock,
            $productRepositoryMock
        );

        $calculatedAvailability = $productAvailabilityCalculation->calculateAvailability($product);

        $this->assertSame($this->getReference($expectedCalculatedAvailability), $calculatedAvailability);
    }

    public function getTestCalculateAvailabilityData()
    {
        return [
            [
                'usingStock' => false,
                'stockQuantity' => null,
                'outOfStockAction' => null,
                'availability' => AvailabilityDataFixture::AVAILABILITY_IN_STOCK,
                'outOfStockAvailability' => null,
                'defaultInStockAvailability' => AvailabilityDataFixture::AVAILABILITY_IN_STOCK,
                'calculatedAvailability' => AvailabilityDataFixture::AVAILABILITY_IN_STOCK,
            ],
            [
                'usingStock' => true,
                'stockQuantity' => null,
                'outOfStockAction' => Product::OUT_OF_STOCK_ACTION_HIDE,
                'availability' => AvailabilityDataFixture::AVAILABILITY_IN_STOCK,
                'outOfStockAvailability' => null,
                'defaultInStockAvailability' => AvailabilityDataFixture::AVAILABILITY_IN_STOCK,
                'calculatedAvailability' => AvailabilityDataFixture::AVAILABILITY_IN_STOCK,
            ],
            [
                'usingStock' => true,
                'stockQuantity' => 5,
                'outOfStockAction' => Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY,
                'availability' => null,
                'outOfStockAvailability' => AvailabilityDataFixture::AVAILABILITY_OUT_OF_STOCK,
                'defaultInStockAvailability' => AvailabilityDataFixture::AVAILABILITY_IN_STOCK,
                'calculatedAvailability' => AvailabilityDataFixture::AVAILABILITY_IN_STOCK,
            ],
            [
                'usingStock' => true,
                'stockQuantity' => 0,
                'outOfStockAction' => Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY,
                'availability' => null,
                'outOfStockAvailability' => AvailabilityDataFixture::AVAILABILITY_OUT_OF_STOCK,
                'defaultInStockAvailability' => AvailabilityDataFixture::AVAILABILITY_IN_STOCK,
                'calculatedAvailability' => AvailabilityDataFixture::AVAILABILITY_OUT_OF_STOCK,
            ],
            [
                'usingStock' => true,
                'stockQuantity' => -1,
                'outOfStockAction' => Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY,
                'availability' => null,
                'outOfStockAvailability' => AvailabilityDataFixture::AVAILABILITY_OUT_OF_STOCK,
                'defaultInStockAvailability' => AvailabilityDataFixture::AVAILABILITY_IN_STOCK,
                'calculatedAvailability' => AvailabilityDataFixture::AVAILABILITY_OUT_OF_STOCK,
            ],
        ];
    }

    public function testCalculateAvailabilityMainVariantWithNoSellableVariants()
    {
        $productData = $this->productDataFactory->create();
        $productData->availability = $this->getReference(AvailabilityDataFixture::AVAILABILITY_ON_REQUEST);
        $this->setProductTypes($productData);
        $this->setVats($productData);

        $variant = Product::create($productData);

        $mainVariantData = $this->productDataFactory->create();
        $this->setProductTypes($mainVariantData);
        $this->setVats($mainVariantData);
        $mainVariant = Product::createMainVariant($mainVariantData, [$variant]);

        $availabilityFacadeMock = $this->getMockBuilder(AvailabilityFacade::class)
            ->setMethods(['getDefaultInStockAvailability'])
            ->disableOriginalConstructor()
            ->getMock();
        $defaultInStockAvailability = $this->getReference(AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $availabilityFacadeMock
            ->expects($this->any())
            ->method('getDefaultInStockAvailability')
            ->willReturn($defaultInStockAvailability);
        $productSellingDeniedRecalculatorMock = $this->createMock(ProductSellingDeniedRecalculator::class);
        $productVisibilityFacadeMock = $this->createMock(ProductVisibilityFacade::class);
        $entityManagerMock = $this->createMock(EntityManager::class);

        $productRepositoryMock = $this->createMock(ProductRepository::class);

        $productAvailabilityCalculation = new ProductAvailabilityCalculation(
            $availabilityFacadeMock,
            $productSellingDeniedRecalculatorMock,
            $productVisibilityFacadeMock,
            $entityManagerMock,
            $productRepositoryMock
        );

        //$variant->setCalculatedAvailability($productAvailabilityCalculation->calculateAvailability($variant));

        $mainVariantCalculatedAvailability = $productAvailabilityCalculation->calculateAvailability($mainVariant);

        $this->assertSame($defaultInStockAvailability, $mainVariantCalculatedAvailability);
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    private function setVats(ProductData $productData): void
    {
        $productVatsIndexedByDomainId = [];
        foreach ($this->domain->getAllIds() as $domainId) {
            $productVatsIndexedByDomainId[$domainId] = $this->vatFacade->getDefaultVatForDomain($domainId);
        }
        $productData->vatsIndexedByDomainId = $productVatsIndexedByDomainId;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    private function setProductTypes(ProductData $productData): void
    {
        /** @var \App\Model\Product\Type\ProductType $productType */
        $productType = $this->getReference(ProductTypeDataFixture::TYPE_COMMON);

        foreach ($this->domain->getAllIds() as $domainId) {
            $productData->productType[$domainId] = $productType;
        }
    }
}
