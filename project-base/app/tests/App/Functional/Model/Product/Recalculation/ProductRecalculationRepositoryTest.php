<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Recalculation;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationRepository;
use Tests\App\Test\TransactionFunctionalTestCase;

class ProductRecalculationRepositoryTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ProductRecalculationRepository $productRecalculationRepository;

    /**
     * @param int[] $inputIds
     * @param int[] $expectedIds
     */
    #[DataProvider('getProductsForRecalculationProvider')]
    public function testProperIdsAreReturned(array $inputIds, array $expectedIds): void
    {
        $calculatedIds = $this->productRecalculationRepository->getIdsToRecalculate($inputIds);

        $this->assertSame($expectedIds, $calculatedIds);
    }

    /**
     * @return iterable
     */
    public static function getProductsForRecalculationProvider(): iterable
    {
        yield 'regular products only' => [
            'inputIds' => [1, 2, 3],
            'expectedIds' => [1, 2, 3],
        ];

        yield 'variant has also other Variants along with MainVariant' => [
            'inputIds' => [153], // variant
            'expectedIds' => [74, 75, 152, 153, 82], // variants first, then main variant
        ];

        yield 'mainVariant has also Variants' => [
            'inputIds' => [69], // main variant
            'expectedIds' => [53, 54, 148, 149, 150, 151, 69], // variants first, then main variant
        ];

        yield 'variants and MainVariant in input does not duplicate ids' => [
            'inputIds' => [148, 69, 151], // variant, main variant, variant
            'expectedIds' => [53, 54, 148, 149, 150, 151, 69], // variants first, then main variant
        ];

        yield 'ids are properly sorted for variants and main variant' => [
            'inputIds' => [54, 53, 151, 69, 149, 150, 148], // randomized input order
            'expectedIds' => [53, 54, 148, 149, 150, 151, 69], // variants first, then main variant
        ];

        yield 'combination of multiple regular products, variants and main variant' => [
            'inputIds' => [2, 75, 3, 69], // regular product, variant, regular product, main variant
            'expectedIds' => [53, 54, 148, 149, 150, 151, 74, 75, 152, 153, 2, 3, 69, 82], // variants first, then regular products and main variant
        ];
    }
}
