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
        $calculatedIds = $this->productRecalculationRepository->getRelevantIdsToRecalculate($inputIds);

        $this->assertEqualsCanonicalizing($expectedIds, $calculatedIds);
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

        yield 'mainVariant has also Variants' => [
            'inputIds' => [69], // main variant
            'expectedIds' => [53, 54, 69, 148, 149, 150, 151], // main variant and its variants
        ];

        yield 'multiple main variants returns all variants' => [
            'inputIds' => [82, 69], // main variant, main variant
            'expectedIds' => [53, 54, 69, 74, 75, 82, 148, 149, 150, 151, 152, 153], // main variants and their variants
        ];

        yield 'combination of multiple regular products, main variants' => [
            'inputIds' => [2, 82, 3, 69], // regular product, main variant, regular product, main variant
            'expectedIds' => [2, 3, 53, 54, 69, 74, 75, 82, 148, 149, 150, 151, 152, 153], // regular products, main variants, and their variants
        ];
    }

    /**
     * @param int[] $inputIds
     * @param int[] $expectedIds
     */
    #[DataProvider('getIdsForVariantReplacementProvider')]
    public function testVariantIdsAreReplaced(array $inputIds, array $expectedIds): void
    {
        $calculatedIds = $this->productRecalculationRepository->replaceVariantIdsWithMainVariantIds($inputIds);

        $this->assertEqualsCanonicalizing($expectedIds, $calculatedIds);
    }

    /**
     * @return iterable
     */
    public static function getIdsForVariantReplacementProvider(): iterable
    {
        yield 'regular products only' => [
            'inputIds' => [1, 2, 3],
            'expectedIds' => [1, 2, 3],
        ];

        yield 'variant is replaced' => [
            'inputIds' => [151], // variant
            'expectedIds' => [69], // main variant
        ];

        yield 'multiple variants with different main variant' => [
            'inputIds' => [74, 150], // variants with different main variant
            'expectedIds' => [69, 82], // main variant
        ];

        yield 'multiple variants with same main variant' => [
            'inputIds' => [150, 151], // variants with the same main variant
            'expectedIds' => [69], // main variant
        ];

        yield 'regular product with variant and main variant' => [
            'inputIds' => [2, 151, 69], // regular product, variant, main variant
            'expectedIds' => [2, 69], // regular product, main variant
        ];
    }
}
