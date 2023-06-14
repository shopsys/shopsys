<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\ProductDataFixture;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator;
use Tests\App\Test\TransactionFunctionalTestCase;

class ProductSellingDeniedRecalculatorTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ProductSellingDeniedRecalculator $productSellingDeniedRecalculator;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductDataFactoryInterface $productDataFactory;

    public function testCalculateSellingDeniedForProductSellableVariant()
    {
        /** @var \App\Model\Product\Product $variant1 */
        $variant1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '53');
        /** @var \App\Model\Product\Product $variant2 */
        $variant2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '54');
        /** @var \App\Model\Product\Product $variant3 */
        $variant3 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '148');
        /** @var \App\Model\Product\Product $mainVariant */
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '69');
        $this->productSellingDeniedRecalculator->calculateSellingDeniedForProduct($mainVariant);

        $variant1productData = $this->productDataFactory->createFromProduct($variant1);
        $variant1productData->sellingDenied = true;
        $this->productFacade->edit($variant1->getId(), $variant1productData);

        /** @var \App\Model\Product\ProductData $variant3productData */
        $variant3productData = $this->productDataFactory->createFromProduct($variant3);

        foreach ($this->domain->getAll() as $domainConfig) {
            $variant3productData->saleExclusion[$domainConfig->getId()] = true;
        }
        $this->productFacade->edit($variant3->getId(), $variant3productData);

        $this->em->refresh($variant1);
        $this->em->refresh($variant2);
        $this->em->refresh($variant3);
        $this->em->refresh($mainVariant);

        $this->assertTrue($variant1->getCalculatedSellingDenied());
        $this->assertFalse($variant2->getCalculatedSellingDenied());
        $this->assertFalse($variant3->getCalculatedSellingDenied());
        $this->assertFalse($mainVariant->getCalculatedSellingDenied());
    }

    public function testCalculateSellingDeniedForProductNotSellableVariants()
    {
        /** @var \App\Model\Product\Product $variant1 */
        $variant1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '53');
        /** @var \App\Model\Product\Product $variant2 */
        $variant2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '54');
        /** @var \App\Model\Product\Product $variant3 */
        $variant3 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '148');
        /** @var \App\Model\Product\Product $variant4 */
        $variant4 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '149');
        /** @var \App\Model\Product\Product $variant5 */
        $variant5 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '150');
        /** @var \App\Model\Product\Product $variant6 */
        $variant6 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '151');
        /** @var \App\Model\Product\Product $mainVariant */
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '69');

        $variant1productData = $this->productDataFactory->createFromProduct($variant1);
        $variant1productData->sellingDenied = true;
        $this->productFacade->edit($variant1->getId(), $variant1productData);
        $variant2productData = $this->productDataFactory->createFromProduct($variant2);
        $variant2productData->sellingDenied = true;
        $this->productFacade->edit($variant2->getId(), $variant2productData);
        $variant3productData = $this->productDataFactory->createFromProduct($variant3);
        $variant3productData->sellingDenied = true;
        $this->productFacade->edit($variant3->getId(), $variant3productData);
        $variant4productData = $this->productDataFactory->createFromProduct($variant4);
        $variant4productData->sellingDenied = true;
        $this->productFacade->edit($variant4->getId(), $variant4productData);
        $variant5productData = $this->productDataFactory->createFromProduct($variant5);
        $variant5productData->sellingDenied = true;
        $this->productFacade->edit($variant5->getId(), $variant5productData);
        $variant6productData = $this->productDataFactory->createFromProduct($variant6);
        $variant6productData->sellingDenied = true;
        $this->productFacade->edit($variant6->getId(), $variant6productData);

        $this->productSellingDeniedRecalculator->calculateSellingDeniedForProduct($mainVariant);

        $this->em->refresh($variant1);
        $this->em->refresh($variant2);
        $this->em->refresh($variant3);
        $this->em->refresh($variant4);
        $this->em->refresh($variant5);
        $this->em->refresh($variant6);
        $this->em->refresh($mainVariant);

        $this->assertTrue($variant1->getCalculatedSellingDenied());
        $this->assertTrue($variant2->getCalculatedSellingDenied());
        $this->assertTrue($variant3->getCalculatedSellingDenied());
        $this->assertTrue($variant4->getCalculatedSellingDenied());
        $this->assertTrue($variant5->getCalculatedSellingDenied());
        $this->assertTrue($variant6->getCalculatedSellingDenied());
        $this->assertTrue($mainVariant->getCalculatedSellingDenied());
    }

    public function testCalculateSellingDeniedForProductNotSellableMainVariant()
    {
        /** @var \App\Model\Product\Product $variant1 */
        $variant1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '53');
        /** @var \App\Model\Product\Product $variant2 */
        $variant2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '54');
        /** @var \App\Model\Product\Product $mainVariant */
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '69');

        $mainVariantproductData = $this->productDataFactory->createFromProduct($mainVariant);
        $mainVariantproductData->sellingDenied = true;
        $this->productFacade->edit($mainVariant->getId(), $mainVariantproductData);

        $this->em->refresh($variant1);
        $this->em->refresh($variant2);
        $this->em->refresh($mainVariant);

        $this->assertTrue($variant1->getCalculatedSellingDenied());
        $this->assertTrue($variant2->getCalculatedSellingDenied());
        $this->assertTrue($mainVariant->getCalculatedSellingDenied());
    }

    public function testPropagationCalculatedSaleExclusionToCalculateSellingDeniedForVariant()
    {
        /** @var \App\Model\Product\Product $variant1 */
        $variant1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '53');

        /** @var \App\Model\Product\ProductData $variant1ProductData */
        $variant1ProductData = $this->productDataFactory->createFromProduct($variant1);
        $variant1ProductData->sellingDenied = false;
        $variant1ProductData->preorder = false;

        foreach ($variant1ProductData->stockProductData as &$stockProductData) {
            $stockProductData->productQuantity = 0;
        }

        $this->productFacade->edit($variant1->getId(), $variant1ProductData);

        $this->em->clear();

        $variant1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '53');

        foreach ($this->domain->getAll() as $domainConfig) {
            $this->assertTrue($variant1->getCalculatedSaleExclusion($domainConfig->getId()));
        }
        $this->assertTrue($variant1->getCalculatedSellingDenied());
    }
}
