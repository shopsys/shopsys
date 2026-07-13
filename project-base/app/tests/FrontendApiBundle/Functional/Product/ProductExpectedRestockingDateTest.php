<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityStatusEnum;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class ProductExpectedRestockingDateTest extends GraphQlTestCase
{
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
}
