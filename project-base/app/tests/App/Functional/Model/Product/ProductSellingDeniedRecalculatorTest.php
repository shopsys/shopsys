<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

class ProductSellingDeniedRecalculatorTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    public function testCalculateSellingDeniedForProductSellableVariant(): void
    {
        $variant1 = $this->productFacade->getById(53);
        $variant3 = $this->productFacade->getById(148);

        $variant1productData = $this->productDataFactory->createFromProduct($variant1);
        $variant1productData->sellingDenied = true;
        $this->productFacade->edit($variant1->getId(), $variant1productData);

        /** @var \App\Model\Product\ProductData $variant3productData */
        $variant3productData = $this->productDataFactory->createFromProduct($variant3);

        foreach ($this->domain->getAll() as $domainConfig) {
            $variant3productData->saleExclusion[$domainConfig->getId()] = true;
        }
        $this->productFacade->edit($variant3->getId(), $variant3productData);

        $this->handleDispatchedRecalculationMessages();

        $this->em->clear();

        $variant1 = $this->productFacade->getById(53);
        $variant2 = $this->productFacade->getById(54);
        $variant3 = $this->productFacade->getById(148);
        $mainVariant = $this->productFacade->getById(69);

        $this->assertTrue($variant1->getCalculatedSellingDenied());
        $this->assertFalse($variant2->getCalculatedSellingDenied());
        $this->assertFalse($variant3->getCalculatedSellingDenied());
        $this->assertFalse($mainVariant->getCalculatedSellingDenied());
    }

    public function testCalculateSellingDeniedForProductNotSellableVariants(): void
    {
        $variant1 = $this->productFacade->getById(53);
        $variant2 = $this->productFacade->getById(54);
        $variant3 = $this->productFacade->getById(148);
        $variant4 = $this->productFacade->getById(149);
        $variant5 = $this->productFacade->getById(150);
        $variant6 = $this->productFacade->getById(151);

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

        $this->handleDispatchedRecalculationMessages();

        $this->em->clear();

        $variant1 = $this->productFacade->getById(53);
        $variant2 = $this->productFacade->getById(54);
        $variant3 = $this->productFacade->getById(148);
        $variant4 = $this->productFacade->getById(149);
        $variant5 = $this->productFacade->getById(150);
        $variant6 = $this->productFacade->getById(151);
        $mainVariant = $this->productFacade->getById(69);

        $this->assertTrue($variant1->getCalculatedSellingDenied());
        $this->assertTrue($variant2->getCalculatedSellingDenied());
        $this->assertTrue($variant3->getCalculatedSellingDenied());
        $this->assertTrue($variant4->getCalculatedSellingDenied());
        $this->assertTrue($variant5->getCalculatedSellingDenied());
        $this->assertTrue($variant6->getCalculatedSellingDenied());
        $this->assertTrue($mainVariant->getCalculatedSellingDenied());
    }

    public function testCalculateSellingDeniedForProductNotSellableMainVariant(): void
    {
        $mainVariant = $this->productFacade->getById(69);

        $mainVariantProductData = $this->productDataFactory->createFromProduct($mainVariant);
        $mainVariantProductData->sellingDenied = true;
        $this->productFacade->edit($mainVariant->getId(), $mainVariantProductData);

        $this->handleDispatchedRecalculationMessages();

        $this->em->clear();

        $variant1 = $this->productFacade->getById(53);
        $variant2 = $this->productFacade->getById(54);
        $mainVariant = $this->productFacade->getById(69);

        $this->assertTrue($variant1->getCalculatedSellingDenied());
        $this->assertTrue($variant2->getCalculatedSellingDenied());
        $this->assertTrue($mainVariant->getCalculatedSellingDenied());
    }

    public function testPropagationCalculatedSaleExclusionToCalculateSellingDeniedForVariant(): void
    {
        $variant1 = $this->productFacade->getById(53);

        /** @var \App\Model\Product\ProductData $variant1ProductData */
        $variant1ProductData = $this->productDataFactory->createFromProduct($variant1);
        $variant1ProductData->sellingDenied = false;

        foreach ($variant1ProductData->productStockData as $productStockData) {
            $productStockData->productQuantity = 0;
        }

        $this->productFacade->edit($variant1->getId(), $variant1ProductData);

        $this->handleDispatchedRecalculationMessages();

        $this->em->clear();

        $variant1 = $this->productFacade->getById(53);

        foreach ($this->domain->getAll() as $domainConfig) {
            $this->assertTrue($variant1->getCalculatedSaleExclusion($domainConfig->getId()));
        }
        $this->assertTrue($variant1->getCalculatedSellingDenied());
    }
}
