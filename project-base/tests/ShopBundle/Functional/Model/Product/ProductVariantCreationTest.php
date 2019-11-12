<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\ShopBundle\DataFixtures\Demo\AvailabilityDataFixture;
use Shopsys\ShopBundle\Model\Product\ProductData;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

final class ProductVariantCreationTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     * @inject
     */
    private $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade
     * @inject
     */
    private $productVariantFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface
     * @inject
     */
    private $productDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     * @inject
     */
    private $vatFacade;

    /**
     * @return array
     */
    public function variantsWithAvailabilitiesCanBeCreatedProvider(): array
    {
        return [
            [AvailabilityDataFixture::AVAILABILITY_IN_STOCK],
            [AvailabilityDataFixture::AVAILABILITY_ON_REQUEST],
            [AvailabilityDataFixture::AVAILABILITY_OUT_OF_STOCK],
            [AvailabilityDataFixture::AVAILABILITY_PREPARING],
        ];
    }

    /**
     * @dataProvider variantsWithAvailabilitiesCanBeCreatedProvider
     * @param string $availabilityReference
     */
    public function testVariantsWithAvailabilitiesCanBeCreated(string $availabilityReference): void
    {
        $productData = $this->productDataFactory->create();
        $productData->availability = $this->getReference($availabilityReference);
        $this->setVats($productData);

        $mainProduct = $this->productFacade->create($productData);
        $secondProduct = $this->productFacade->create($productData);
        $thirdProduct = $this->productFacade->create($productData);

        $mainVariant = $this->productVariantFacade->createVariant($mainProduct, [$secondProduct, $thirdProduct]);

        $this->assertTrue($mainVariant->isMainVariant());
        $this->assertContainsAllVariants([$mainProduct, $secondProduct, $thirdProduct], $mainVariant);
    }

    /**
     * @return array
     */
    public function variantsWithStockCanBeCreatedProvider(): array
    {
        return [
            [0, Product::OUT_OF_STOCK_ACTION_EXCLUDE_FROM_SALE, null],
            [100, Product::OUT_OF_STOCK_ACTION_EXCLUDE_FROM_SALE, null],
            [0, Product::OUT_OF_STOCK_ACTION_HIDE, null],
            [100, Product::OUT_OF_STOCK_ACTION_HIDE, null],
            [0, Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY, AvailabilityDataFixture::AVAILABILITY_IN_STOCK],
            [100, Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY, AvailabilityDataFixture::AVAILABILITY_IN_STOCK],
            [0, Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY, AvailabilityDataFixture::AVAILABILITY_ON_REQUEST],
            [100, Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY, AvailabilityDataFixture::AVAILABILITY_ON_REQUEST],
            [0, Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY, AvailabilityDataFixture::AVAILABILITY_OUT_OF_STOCK],
            [100, Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY, AvailabilityDataFixture::AVAILABILITY_OUT_OF_STOCK],
            [0, Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY, AvailabilityDataFixture::AVAILABILITY_PREPARING],
            [100, Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY, AvailabilityDataFixture::AVAILABILITY_PREPARING],
        ];
    }

    /**
     * @dataProvider variantsWithStockCanBeCreatedProvider
     * @param int $quantity
     * @param string $outOfStockAction
     * @param string|null $outOfStockAvailabilityReference
     */
    public function testVariantsWithStockCanBeCreated(int $quantity, string $outOfStockAction, ?string $outOfStockAvailabilityReference): void
    {
        $productData = $this->productDataFactory->create();
        $productData->usingStock = true;
        $productData->stockQuantity = $quantity;
        $productData->outOfStockAction = $outOfStockAction;
        if ($outOfStockAvailabilityReference !== null) {
            $productData->outOfStockAvailability = $this->getReference($outOfStockAvailabilityReference);
        }
        $this->setVats($productData);

        $mainProduct = $this->productFacade->create($productData);
        $secondProduct = $this->productFacade->create($productData);
        $thirdProduct = $this->productFacade->create($productData);

        $mainVariant = $this->productVariantFacade->createVariant($mainProduct, [$secondProduct, $thirdProduct]);

        $this->assertTrue($mainVariant->isMainVariant());
        $this->assertContainsAllVariants([$mainProduct, $secondProduct, $thirdProduct], $mainVariant);
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Product\Product[] $expectedVariants
     * @param \Shopsys\ShopBundle\Model\Product\Product $mainVariant
     */
    private function assertContainsAllVariants(array $expectedVariants, Product $mainVariant): void
    {
        $actualVariants = $mainVariant->getVariants();
        $this->assertCount(count($expectedVariants), $actualVariants);
        foreach ($expectedVariants as $expectedVariant) {
            $this->assertContains($expectedVariant, $actualVariants);
        }
        foreach ($actualVariants as $actualVariant) {
            $this->assertTrue($actualVariant->isVariant());
        }
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Product\ProductData $productData
     */
    private function setVats(ProductData $productData): void
    {
        $productVatsIndexedByDomainId = [];
        foreach ($this->domain->getAllIds() as $domainId) {
            $productVatsIndexedByDomainId[$domainId] = $this->vatFacade->getDefaultVatFormDomain($domainId);
        }
        $productData->vatsIndexedByDomainId = $productVatsIndexedByDomainId;
    }
}
