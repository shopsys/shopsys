<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Pricing\SpecialPrice;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceRepository;
use Tests\App\Test\FunctionalTestCase;

final class SpecialPriceRepositoryTest extends FunctionalTestCase
{
    private const array PRODUCT_IDS = ['1', '72', '117', '19', '4'];

    /**
     * @inject
     */
    private SpecialPriceRepository $specialPriceRepository;

    public function testRelevantSpecialPricesLoadedForManyProductsMatchTheOnesLoadedPerProduct(): void
    {
        $domainId = $this->domain->getId();
        $products = array_map(
            fn (string $productId): Product => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productId, Product::class),
            self::PRODUCT_IDS,
        );
        $productIds = array_map(static fn (Product $product): int => $product->getId(), $products);

        $relevantSpecialPricesIndexedByProductId = $this->specialPriceRepository->getRelevantSpecialPricesByProductIdsIndexedByProductId($productIds, $domainId);

        $this->assertNotEmpty($relevantSpecialPricesIndexedByProductId);

        foreach ($products as $product) {
            $this->assertEquals(
                $this->specialPriceRepository->findRelevantSpecialPrice($product, $domainId),
                $relevantSpecialPricesIndexedByProductId[$product->getId()] ?? null,
            );
        }
    }
}
