<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityStatusEnum;
use Symfony\Component\Clock\DatePoint;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;
use function sleep;

final class ProductExpectedRestockingDateTest extends GraphQlTestCase
{
    /**
     * A main variant with two variants ('7700769XCX' from the demo data)
     */
    private const int MAIN_VARIANT_REFERENCE_ID = 83;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    /**
     * @return iterable<string, array{productReferenceId: int, isDateExpectedInResponse: bool, expectedAvailabilityStatus: string}>
     */
    public static function getExpectedRestockingDateData(): iterable
    {
        yield 'out of stock product with future restocking date is expecting' => [
            'productReferenceId' => 10,
            'isDateExpectedInResponse' => true,
            'expectedAvailabilityStatus' => AvailabilityStatusEnum::EXPECTED_RESTOCK,
        ];

        yield 'in-stock product exposes the date but keeps in-stock availability' => [
            'productReferenceId' => 4,
            'isDateExpectedInResponse' => true,
            'expectedAvailabilityStatus' => AvailabilityStatusEnum::IN_STOCK,
        ];

        yield 'out of stock product with passed restocking date stays out of stock' => [
            'productReferenceId' => 21,
            'isDateExpectedInResponse' => false,
            'expectedAvailabilityStatus' => AvailabilityStatusEnum::OUT_OF_STOCK,
        ];

        yield 'product with denied negative stock is expecting as well' => [
            'productReferenceId' => 22,
            'isDateExpectedInResponse' => true,
            'expectedAvailabilityStatus' => AvailabilityStatusEnum::EXPECTED_RESTOCK,
        ];
    }

    #[DataProvider('getExpectedRestockingDateData')]
    public function testExpectedRestockingDateInProductQuery(
        int $productReferenceId,
        bool $isDateExpectedInResponse,
        string $expectedAvailabilityStatus,
    ): void {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productReferenceId, Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ExpectedRestockingDateQuery.graphql', [
            'uuid' => $product->getUuid(),
        ]);

        $responseData = $this->getResponseDataForGraphQlType($response, 'product');

        $expectedDate = $isDateExpectedInResponse
            ? $product->getExpectedRestockingDate()?->format(DATE_ATOM)
            : null;

        if ($isDateExpectedInResponse) {
            $this->assertNotNull($expectedDate);
        }

        $this->assertSame($expectedDate, $responseData['expectedRestockingDate']);
        $this->assertSame($expectedAvailabilityStatus, $responseData['availability']['status']);
    }

    public function testMainVariantIsExpectingTheEarliestRestockingDateOfItsVariants(): void
    {
        $mainVariant = $this->getReference(
            ProductDataFixture::PRODUCT_PREFIX . self::MAIN_VARIANT_REFERENCE_ID,
            Product::class,
        );
        $earliestRestockingDate = new DatePoint('midnight +7 days');
        $restockingDates = [new DatePoint('midnight +14 days'), $earliestRestockingDate];

        foreach ($mainVariant->getVariants() as $index => $variant) {
            $this->editVariant($variant, 0, $restockingDates[$index]);
        }

        $this->handleDispatchedRecalculationMessages();

        // wait for elastic to reindex
        sleep(1);

        $responseData = $this->getMainVariantResponseData($mainVariant);

        $this->assertSame($earliestRestockingDate->format(DATE_ATOM), $responseData['expectedRestockingDate']);
        $this->assertSame(AvailabilityStatusEnum::EXPECTED_RESTOCK, $responseData['availability']['status']);
    }

    public function testRestockingDateOfHiddenVariantIsIgnoredForTheMainVariant(): void
    {
        $mainVariant = $this->getReference(
            ProductDataFixture::PRODUCT_PREFIX . self::MAIN_VARIANT_REFERENCE_ID,
            Product::class,
        );
        [$hiddenVariant, $visibleVariant] = $mainVariant->getVariants();
        $visibleVariantRestockingDate = new DatePoint('midnight +14 days');

        // the hidden variant would win with its earlier restocking date, but it must not be considered
        $this->editVariant($hiddenVariant, 0, new DatePoint('midnight +7 days'), true);
        $this->editVariant($visibleVariant, 0, $visibleVariantRestockingDate);

        $this->handleDispatchedRecalculationMessages();

        // wait for elastic to reindex
        sleep(1);

        $responseData = $this->getMainVariantResponseData($mainVariant);

        $this->assertSame($visibleVariantRestockingDate->format(DATE_ATOM), $responseData['expectedRestockingDate']);
        $this->assertSame(AvailabilityStatusEnum::EXPECTED_RESTOCK, $responseData['availability']['status']);
    }

    public function testStockOfHiddenVariantDoesNotMakeTheMainVariantAvailable(): void
    {
        $mainVariant = $this->getReference(
            ProductDataFixture::PRODUCT_PREFIX . self::MAIN_VARIANT_REFERENCE_ID,
            Product::class,
        );
        [$hiddenVariant, $visibleVariant] = $mainVariant->getVariants();

        // the hidden variant is the only one with a stock quantity, but it must not be considered
        $this->editVariant($hiddenVariant, 100, null, true);
        $this->editVariant($visibleVariant, 0);

        $this->handleDispatchedRecalculationMessages();

        // wait for elastic to reindex
        sleep(1);

        $responseData = $this->getMainVariantResponseData($mainVariant);

        $this->assertNull($responseData['expectedRestockingDate']);
        $this->assertSame(AvailabilityStatusEnum::OUT_OF_STOCK, $responseData['availability']['status']);
    }

    /**
     * @return array<string, mixed>
     */
    private function getMainVariantResponseData(Product $mainVariant): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ExpectedRestockingDateQuery.graphql', [
            'uuid' => $mainVariant->getUuid(),
        ]);

        return $this->getResponseDataForGraphQlType($response, 'product');
    }

    private function editVariant(
        Product $variant,
        int $stockQuantity,
        ?DateTimeImmutable $expectedRestockingDate = null,
        bool $hidden = false,
    ): void {
        $productData = $this->productDataFactory->createFromProduct($variant);
        $productData->expectedRestockingDate = $expectedRestockingDate;
        $productData->hidden = $hidden;

        foreach ($productData->productStockData as $productStockData) {
            $productStockData->productQuantity = $stockQuantity;
        }

        $this->productFacade->edit($variant->getId(), $productData);
    }
}
