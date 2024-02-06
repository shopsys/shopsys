<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\ProductDataFixture;
use Shopsys\FrameworkBundle\Model\Product\Product;
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
        /** @var \App\Model\Product\Product $mainProduct */
        $mainProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '7');

        $variants = [
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '8'),
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '88'),
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '89'),
        ];

        $mainVariant = $this->productVariantFacade->createVariant($mainProduct, $variants);

        $this->assertTrue($mainVariant->isMainVariant());
        $this->assertContainsAllVariants($variants, $mainVariant);
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
}
