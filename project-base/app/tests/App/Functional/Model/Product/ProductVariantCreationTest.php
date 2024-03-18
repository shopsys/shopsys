<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

final class ProductVariantCreationTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ProductVariantFacade $productVariantFacade;

    public function testVariantWithImageCanBeCreated(): void
    {
        $mainProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '7', Product::class);

        $variants = [
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '8', Product::class),
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '88', Product::class),
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '89', Product::class),
        ];

        /** @var \App\Model\Product\Product $mainVariant */
        $mainVariant = $this->productVariantFacade->createVariant($mainProduct, $variants);

        $this->assertTrue($mainVariant->isMainVariant());
        $this->assertContainsAllVariants($variants, $mainVariant);
    }

    /**
     * @param \App\Model\Product\Product[] $expectedVariants
     * @param \App\Model\Product\Product $mainVariant
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
}
