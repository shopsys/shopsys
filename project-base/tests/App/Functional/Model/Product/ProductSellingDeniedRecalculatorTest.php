<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\ProductDataFixture;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class ProductSellingDeniedRecalculatorTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator
     * @inject
     */
    private $productSellingDeniedRecalculator;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     * @inject
     */
    private $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface
     * @inject
     */
    private $productDataFactory;

    public function testCalculateSellingDeniedForProductSellableVariant()
    {
        $em = $this->getEntityManager();

        /** @var \App\Model\Product\Product $variant1 */
        $variant1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '53');
        /** @var \App\Model\Product\Product $variant2 */
        $variant2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '54');
        /** @var \App\Model\Product\Product $variant3 */
        $variant3 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '69');
        /** @var \App\Model\Product\Product $mainVariant */
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '148');

        $variant1productData = $this->productDataFactory->createFromProduct($variant1);
        $variant1productData->sellingDenied = true;
        $this->productFacade->edit($variant1->getId(), $variant1productData);

        $this->productSellingDeniedRecalculator->calculateSellingDeniedForProduct($variant1);

        $em->refresh($variant1);
        $em->refresh($variant2);
        $em->refresh($variant3);
        $em->refresh($mainVariant);

        $this->assertTrue($variant1->getCalculatedSellingDenied());
        $this->assertFalse($variant2->getCalculatedSellingDenied());
        $this->assertFalse($variant3->getCalculatedSellingDenied());
        $this->assertFalse($mainVariant->getCalculatedSellingDenied());
    }

    public function testCalculateSellingDeniedForProductNotSellableVariants()
    {
        $em = $this->getEntityManager();

        /** @var \App\Model\Product\Product $variant1 */
        $variant1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '53');
        /** @var \App\Model\Product\Product $variant2 */
        $variant2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '54');
        /** @var \App\Model\Product\Product $variant3 */
        $variant3 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '69');
        /** @var \App\Model\Product\Product $mainVariant */
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '148');

        $variant1productData = $this->productDataFactory->createFromProduct($variant1);
        $variant1productData->sellingDenied = true;
        $this->productFacade->edit($variant1->getId(), $variant1productData);
        $variant2productData = $this->productDataFactory->createFromProduct($variant2);
        $variant2productData->sellingDenied = true;
        $this->productFacade->edit($variant2->getId(), $variant2productData);
        $variant3productData = $this->productDataFactory->createFromProduct($variant3);
        $variant3productData->sellingDenied = true;
        $this->productFacade->edit($variant3->getId(), $variant3productData);

        $this->productSellingDeniedRecalculator->calculateSellingDeniedForProduct($mainVariant);

        $em->refresh($variant1);
        $em->refresh($variant2);
        $em->refresh($variant3);
        $em->refresh($mainVariant);

        $this->assertTrue($variant1->getCalculatedSellingDenied());
        $this->assertTrue($variant2->getCalculatedSellingDenied());
        $this->assertTrue($variant3->getCalculatedSellingDenied());
        $this->assertTrue($mainVariant->getCalculatedSellingDenied());
    }

    public function testCalculateSellingDeniedForProductNotSellableMainVariant()
    {
        $em = $this->getEntityManager();

        /** @var \App\Model\Product\Product $variant1 */
        $variant1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '53');
        /** @var \App\Model\Product\Product $variant2 */
        $variant2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '54');
        /** @var \App\Model\Product\Product $variant3 */
        $variant3 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '69');
        /** @var \App\Model\Product\Product $mainVariant */
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '148');

        $mainVariantproductData = $this->productDataFactory->createFromProduct($mainVariant);
        $mainVariantproductData->sellingDenied = true;
        $this->productFacade->edit($mainVariant->getId(), $mainVariantproductData);

        $this->productSellingDeniedRecalculator->calculateSellingDeniedForProduct($mainVariant);

        $em->refresh($variant1);
        $em->refresh($variant2);
        $em->refresh($variant3);
        $em->refresh($mainVariant);

        $this->assertTrue($variant1->getCalculatedSellingDenied());
        $this->assertTrue($variant2->getCalculatedSellingDenied());
        $this->assertTrue($variant3->getCalculatedSellingDenied());
        $this->assertTrue($mainVariant->getCalculatedSellingDenied());
    }
}
