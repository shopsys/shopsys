<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\AvailabilityDataFixture;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

final class ProductVariantCreationTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductVariantFacade $productVariantFacade;

    /**
     * @inject
     */
    private ProductDataFactoryInterface $productDataFactory;

    /**
     * @inject
     */
    private VatFacade $vatFacade;

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

        $productData->catnum = '12345';
        /** @var \App\Model\Product\Product $mainProduct */
        $mainProduct = $this->productFacade->create($productData);

        $productData->catnum = '123456';
        /** @var \App\Model\Product\Product $secondProduct */
        $secondProduct = $this->productFacade->create($productData);

        $productData->catnum = '1234567';
        /** @var \App\Model\Product\Product $thirdProduct */
        $thirdProduct = $this->productFacade->create($productData);

        $mainVariant = $this->productVariantFacade->createVariant($mainProduct, [$secondProduct, $thirdProduct]);

        $this->assertTrue($mainVariant->isMainVariant());
        $this->assertContainsAllVariants([$secondProduct, $thirdProduct], $mainVariant);
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
    public function testVariantsWithStockCanBeCreated(
        int $quantity,
        string $outOfStockAction,
        ?string $outOfStockAvailabilityReference,
    ): void {
        $productData = $this->productDataFactory->create();
        $productData->usingStock = true;
        $productData->stockQuantity = $quantity;
        $productData->outOfStockAction = $outOfStockAction;
        $productData->availability = $this->getReference(AvailabilityDataFixture::AVAILABILITY_IN_STOCK);

        if ($outOfStockAvailabilityReference !== null) {
            $productData->outOfStockAvailability = $this->getReference($outOfStockAvailabilityReference);
        }
        $this->setVats($productData);

        $productData->catnum = '12345';
        /** @var \App\Model\Product\Product $mainProduct */
        $mainProduct = $this->productFacade->create($productData);

        $productData->catnum = '123456';
        /** @var \App\Model\Product\Product $secondProduct */
        $secondProduct = $this->productFacade->create($productData);

        $productData->catnum = '1234567';
        /** @var \App\Model\Product\Product $thirdProduct */
        $thirdProduct = $this->productFacade->create($productData);

        $mainVariant = $this->productVariantFacade->createVariant($mainProduct, [$secondProduct, $thirdProduct]);

        $this->assertTrue($mainVariant->isMainVariant());
        $this->assertContainsAllVariants([$secondProduct, $thirdProduct], $mainVariant);
    }

    /**
     * @param \App\Model\Product\Product[] $expectedVariants
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $mainVariant
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
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $productData
     */
    private function setVats(ProductData $productData): void
    {
        $productVatsIndexedByDomainId = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $productVatsIndexedByDomainId[$domainId] = $this->vatFacade->getDefaultVatForDomain($domainId);
        }
        $productData->vatsIndexedByDomainId = $productVatsIndexedByDomainId;
    }
}
