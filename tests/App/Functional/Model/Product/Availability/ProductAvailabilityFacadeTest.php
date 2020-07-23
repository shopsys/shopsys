<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Availability;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\StocksDataFixture;
use App\Model\Product\ProductData;
use App\Model\Stock\StockSettingsData;
use Tests\App\Test\TransactionFunctionalTestCase;

class ProductAvailabilityFacadeTest extends TransactionFunctionalTestCase
{
    protected const FIRST_DOMAIN_ID = 1;

    /**
     * @var \App\Model\Product\Availability\ProductAvailabilityFacade
     * @inject
     */
    private $productAvailabilityFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface
     * @inject
     */
    private $productDataFactory;

    /**
     * @var \App\Model\Product\ProductFacade
     * @inject
     */
    private $productFacade;

    /**
     * @var \App\Model\Stock\ProductStockDataFactory
     * @inject
     */
    private $productStockDataFactory;

    /**
     * @var \App\Model\Stock\StockSettingsDataFacade
     * @inject
     */
    private $stockSettingsDataFacade;

    public function testShippingDaysByDomainIdForEmptyStock()
    {
        $stockQuantity = 0;

        $stockSettingsData = new StockSettingsData();
        $stockSettingsData->transfer = 10;
        $stockSettingsData->delivery = 20;
        $this->stockSettingsDataFacade->edit($stockSettingsData);

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->createFromProduct($product);

        $this->setupStockQuantityToProductData($productData, $stockQuantity);

        $this->productFacade->edit($product->getId(), $productData);

        $this->em->refresh($product);

        $this->assertEquals($stockSettingsData->delivery, $this->productAvailabilityFacade->getShippingDaysByDomainId($product, self::FIRST_DOMAIN_ID));
    }

    public function testShippingDaysByDomainIdForFullStock()
    {
        $stockQuantity = 5;
        $stockSettingsData = new StockSettingsData();
        $stockSettingsData->transfer = 10;
        $stockSettingsData->delivery = 20;
        $this->stockSettingsDataFacade->edit($stockSettingsData);

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->createFromProduct($product);

        $this->setupStockQuantityToProductData($productData, $stockQuantity);

        $this->productFacade->edit($product->getId(), $productData);

        $this->em->refresh($product);

        $this->assertEquals($stockSettingsData->transfer, $this->productAvailabilityFacade->getShippingDaysByDomainId($product, self::FIRST_DOMAIN_ID));
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param int $stockQuantity
     */
    private function setupStockQuantityToProductData(ProductData $productData, int $stockQuantity): void
    {
        $productData->stockProductData = [];
        foreach ($this->getStocksByDomainId(self::FIRST_DOMAIN_ID) as $stock) {
            $stockProductData = $this->productStockDataFactory->createFromStock($stock);
            $stockProductData->productQuantity = $stockQuantity;
            $productData->stockProductData[] = $stockProductData;
        }
    }

    /**
     * @dataProvider getTestIsProductAvailableOnDomainProvider
     * @param int $stockQuantity
     * @param bool $expectedIsProductAvailableOnDomain
     */
    public function testIsProductAvailableOnDomain(int $stockQuantity, bool $expectedIsProductAvailableOnDomain)
    {

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->createFromProduct($product);

        $productData->stockProductData = [];
        foreach ($this->getStocksByDomainId(self::FIRST_DOMAIN_ID) as $stock) {
            $stockProductData = $this->productStockDataFactory->createFromStock($stock);
            $stockProductData->productQuantity = $stockQuantity;

            $productData->stockProductData[] = $stockProductData;
        }

        $this->productFacade->edit($product->getId(), $productData);

        $this->em->refresh($product);

        $this->assertSame($expectedIsProductAvailableOnDomain, $this->productAvailabilityFacade->isProductAvailableOnDomainCached($product, self::FIRST_DOMAIN_ID));
    }

    /**
     * @return array
     */
    public function getTestIsProductAvailableOnDomainProvider(): array
    {
        return [
            [
                'stockQuantity' => 5,
                'expectedIsProductAvailableOnDomain' => true,
            ],
            [
                'stockQuantity' => 0,
                'expectedIsProductAvailableOnDomain' => false,
            ],
        ];
    }

    /**
     * @dataProvider getTestIsProductAvailableOnDomainOrHasPreorder
     * @param int $stockQuantity
     * @param bool $preorder
     * @param bool $expected
     */
    public function testIsProductAvailableOnDomainOrHasPreorder(int $stockQuantity, bool $preorder, bool $expected)
    {

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->createFromProduct($product);

        $productData->stockProductData = [];
        foreach ($this->getStocksByDomainId(self::FIRST_DOMAIN_ID) as $stock) {
            $stockProductData = $this->productStockDataFactory->createFromStock($stock);
            $stockProductData->productQuantity = $stockQuantity;
            $productData->stockProductData[] = $stockProductData;
        }

        $productData->preorder = $preorder;

        $this->productFacade->edit($product->getId(), $productData);

        $this->em->refresh($product);

        $this->assertSame($expected, $this->productAvailabilityFacade->isProductAvailableOnDomainOrHasPreorder($product, self::FIRST_DOMAIN_ID));
    }

    /**
     * @return array
     */
    public function getTestIsProductAvailableOnDomainOrHasPreorder(): array
    {
        return [
            [
                'stockQuantity' => 5,
                'preorder' => true,
                'expected' => true,
            ],
            [
                'stockQuantity' => 0,
                'preorder' => true,
                'expected' => true,
            ],
            [
                'stockQuantity' => 5,
                'preorder' => false,
                'expected' => true,
            ],
            [
                'stockQuantity' => 0,
                'preorder' => false,
                'expected' => false,
            ],
        ];
    }

    public function testGroupedStockQuantity()
    {
        $stockQuantity = 5;
        $expected = count($this->getStocksByDomainId(self::FIRST_DOMAIN_ID)) * $stockQuantity;

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->createFromProduct($product);

        $productData->stockProductData = [];
        foreach ($this->getStocksByDomainId(self::FIRST_DOMAIN_ID) as $stock) {
            $stockProductData = $this->productStockDataFactory->createFromStock($stock);
            $stockProductData->productQuantity = $stockQuantity;
            $productData->stockProductData[] = $stockProductData;
        }

        $this->productFacade->edit($product->getId(), $productData);

        $this->em->refresh($product);

        $this->assertSame($expected, $this->productAvailabilityFacade->getGroupedStockQuantity($product, self::FIRST_DOMAIN_ID));
    }

    /**
     * @dataProvider getTestProductAvailabilityInformationByDomainIdProvider
     * @param int $stockQuantity
     * @param string $expected
     * @param bool $preorder
     * @param int $transfer
     * @param int $delivery
     * @param int|null $vendorDeliveryDate
     */
    public function testProductAvailabilityInformationByDomainId(
        int $stockQuantity,
        string $expected,
        bool $preorder,
        int $transfer,
        int $delivery,
        ?int $vendorDeliveryDate
    ) {
        $stockSettingsData = new StockSettingsData();
        $stockSettingsData->transfer = $transfer;
        $stockSettingsData->delivery = $delivery;
        $this->stockSettingsDataFacade->edit($stockSettingsData);

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->createFromProduct($product);

        $productData->stockProductData = [];
        foreach ($this->getStocksByDomainId(self::FIRST_DOMAIN_ID) as $stock) {
            $stockProductData = $this->productStockDataFactory->createFromStock($stock);
            $stockProductData->productQuantity = $stockQuantity;
            $productData->stockProductData[] = $stockProductData;
        }
        $productData->preorder = $preorder;
        $productData->vendorDeliveryDate = $vendorDeliveryDate;

        $this->productFacade->edit($product->getId(), $productData);

        $this->em->refresh($product);

        $this->assertSame($expected, $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId($product, self::FIRST_DOMAIN_ID));
    }

    /**
     * @return array
     */
    public function getTestProductAvailabilityInformationByDomainIdProvider(): array
    {
        return [
            [
                'stockQuantity' => 5,
                'expected' => 'Skladem',
                'preorder' => true,
                'transfer' => 10,
                'delivery' => 20,
                'vendorDeliveryDate' => null,
            ],
            [
                'stockQuantity' => 0,
                'expected' => 'Vyprodáno',
                'preorder' => false,
                'transfer' => 10,
                'delivery' => 20,
                'vendorDeliveryDate' => null,
            ],
            [
                'stockQuantity' => 0,
                'expected' => 'K dispozici za týden',
                'preorder' => true,
                'transfer' => 10,
                'delivery' => 0,
                'vendorDeliveryDate' => 0,
            ],
            [
                'stockQuantity' => 0,
                'expected' => 'K dispozici za týden',
                'preorder' => true,
                'transfer' => 10,
                'delivery' => 3,
                'vendorDeliveryDate' => 3,
            ],

            [
                'stockQuantity' => 0,
                'expected' => 'K dispozici za 2 týdny',
                'preorder' => true,
                'transfer' => 10,
                'delivery' => 5,
                'vendorDeliveryDate' => 5,
            ],

            [
                'stockQuantity' => 0,
                'expected' => 'K dispozici za 3 týdny',
                'preorder' => true,
                'transfer' => 10,
                'delivery' => 5,
                'vendorDeliveryDate' => 10,
            ],

            [
                'stockQuantity' => 0,
                'expected' => 'K dispozici za 4 týdny',
                'preorder' => true,
                'transfer' => 10,
                'delivery' => 11,
                'vendorDeliveryDate' => 11,
            ],

            [
                'stockQuantity' => 0,
                'expected' => 'K dispozici za 5 týdnů',
                'preorder' => true,
                'transfer' => 10,
                'delivery' => 15,
                'vendorDeliveryDate' => 15,
            ],

            [
                'stockQuantity' => 0,
                'expected' => 'K dispozici za 6 týdnů',
                'preorder' => true,
                'transfer' => 10,
                'delivery' => 18,
                'vendorDeliveryDate' => 18,
            ],

        ];
    }

    /**
     * @param int $domainId
     * @return \App\Model\Stock\Stock[]
     */
    private function getStocksByDomainId(int $domainId): array
    {
        $stocks = [];
        foreach (StocksDataFixture::getDemoData($domainId) as $demoRow) {
            $stocks[] = $this->getReferenceForDomain(StocksDataFixture::STOCK_PREFIX . $demoRow[StocksDataFixture::ATTR_EXTERNAL], $domainId);
        }

        return $stocks;
    }
}
