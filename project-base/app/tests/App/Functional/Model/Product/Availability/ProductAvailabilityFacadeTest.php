<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Availability;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Availability\ProductAvailabilityFacade;
use App\Model\Product\ProductData;
use App\Model\Product\ProductFacade;
use App\Model\Stock\ProductStockDataFactory;
use App\Model\Stock\StockFacade;
use App\Model\Stock\StockSettingsData;
use App\Model\Stock\StockSettingsDataFacade;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface;
use Tests\App\Test\TransactionFunctionalTestCase;

class ProductAvailabilityFacadeTest extends TransactionFunctionalTestCase
{
    protected const FIRST_DOMAIN_ID = 1;

    /**
     * @inject
     */
    private ProductAvailabilityFacade $productAvailabilityFacade;

    /**
     * @inject
     */
    private ProductDataFactoryInterface $productDataFactory;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductStockDataFactory $productStockDataFactory;

    /**
     * @inject
     */
    private StockSettingsDataFacade $stockSettingsDataFacade;

    /**
     * @inject
     */
    private StockFacade $stockFacade;

    public function testShippingDaysByDomainIdForEmptyStock(): void
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

    public function testShippingDaysByDomainIdForFullStock(): void
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

        foreach ($this->stockFacade->getStocksEnabledOnDomainIndexedByStockId(self::FIRST_DOMAIN_ID) as $stock) {
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
    public function testIsProductAvailableOnDomain(int $stockQuantity, bool $expectedIsProductAvailableOnDomain): void
    {

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->createFromProduct($product);

        $productData->stockProductData = [];

        foreach ($this->stockFacade->getStocksEnabledOnDomainIndexedByStockId(self::FIRST_DOMAIN_ID) as $stock) {
            $stockProductData = $this->productStockDataFactory->createFromStock($stock);
            $stockProductData->productQuantity = $stockQuantity;

            $productData->stockProductData[] = $stockProductData;
        }

        $this->productFacade->edit($product->getId(), $productData);

        $this->em->refresh($product);

        $this->assertSame($expectedIsProductAvailableOnDomain, $this->productAvailabilityFacade->isProductAvailableOnDomainCached($product, self::FIRST_DOMAIN_ID));
    }

    /**
     * @return array<'expectedIsProductAvailableOnDomain'|'stockQuantity', int|true>[]|array<'expectedIsProductAvailableOnDomain'|'stockQuantity', int|false>[]
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
    public function testIsProductAvailableOnDomainOrHasPreorder(int $stockQuantity, bool $preorder, bool $expected): void
    {

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->createFromProduct($product);

        $productData->stockProductData = [];

        foreach ($this->stockFacade->getStocksEnabledOnDomainIndexedByStockId(self::FIRST_DOMAIN_ID) as $stock) {
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
     * @return array<int, array<'expected'|'preorder'|'stockQuantity', int|bool>>
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

    public function testGroupedStockQuantity(): void
    {
        $stockQuantity = 5;
        $expected = count($this->stockFacade->getStocksEnabledOnDomainIndexedByStockId(self::FIRST_DOMAIN_ID)) * $stockQuantity;

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->createFromProduct($product);

        $productData->stockProductData = [];

        foreach ($this->stockFacade->getStocksEnabledOnDomainIndexedByStockId(self::FIRST_DOMAIN_ID) as $stock) {
            $stockProductData = $this->productStockDataFactory->createFromStock($stock);
            $stockProductData->productQuantity = $stockQuantity;
            $productData->stockProductData[] = $stockProductData;
        }

        $this->productFacade->edit($product->getId(), $productData);

        $this->em->refresh($product);

        $this->assertSame($expected, $this->productAvailabilityFacade->getGroupedStockQuantityByProductAndDomainId($product, self::FIRST_DOMAIN_ID));
    }

    /**
     * @dataProvider getTestProductAvailabilityInformationByDomainIdProvider
     * @param int $stockQuantity
     * @param int $expectedWeekCount
     * @param bool $preorder
     * @param int $transfer
     * @param int $delivery
     * @param int|null $vendorDeliveryDate
     */
    public function testProductAvailabilityInformationByDomainId(
        int $stockQuantity,
        int $expectedWeekCount,
        bool $preorder,
        int $transfer,
        int $delivery,
        ?int $vendorDeliveryDate,
    ): void {
        $stockSettingsData = new StockSettingsData();
        $stockSettingsData->transfer = $transfer;
        $stockSettingsData->delivery = $delivery;
        $this->stockSettingsDataFacade->edit($stockSettingsData);

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->createFromProduct($product);

        $productData->stockProductData = [];

        foreach ($this->stockFacade->getStocksEnabledOnDomainIndexedByStockId(self::FIRST_DOMAIN_ID) as $stock) {
            $stockProductData = $this->productStockDataFactory->createFromStock($stock);
            $stockProductData->productQuantity = $stockQuantity;
            $productData->stockProductData[] = $stockProductData;
        }
        $productData->preorder = $preorder;
        $productData->vendorDeliveryDate = $vendorDeliveryDate;

        $this->productFacade->edit($product->getId(), $productData);

        $this->em->refresh($product);

        $expected = match ($expectedWeekCount) {
            -1 => t('Out of stock', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            0 => t('In stock', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            default => t('{0,1} Available in one week|[2,Inf] Available in %count% weeks', ['%count%' => $expectedWeekCount], Translator::DEFAULT_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
        };

        $this->assertSame($expected, $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId($product, self::FIRST_DOMAIN_ID));
    }

    /**
     * @return array<int, array<bool|int|null>>
     */
    public function getTestProductAvailabilityInformationByDomainIdProvider(): array
    {
        return [
            [
                'stockQuantity' => 5,
                'expectedWeekCount' => 0,
                'preorder' => true,
                'transfer' => 10,
                'delivery' => 20,
                'vendorDeliveryDate' => null,
            ],
            [
                'stockQuantity' => 0,
                'expectedWeekCount' => -1,
                'preorder' => false,
                'transfer' => 10,
                'delivery' => 20,
                'vendorDeliveryDate' => null,
            ],
            [
                'stockQuantity' => 0,
                'expectedWeekCount' => 1,
                'preorder' => true,
                'transfer' => 10,
                'delivery' => 0,
                'vendorDeliveryDate' => 0,
            ],
            [
                'stockQuantity' => 0,
                'expectedWeekCount' => 1,
                'preorder' => true,
                'transfer' => 10,
                'delivery' => 3,
                'vendorDeliveryDate' => 3,
            ],

            [
                'stockQuantity' => 0,
                'expectedWeekCount' => 2,
                'preorder' => true,
                'transfer' => 10,
                'delivery' => 5,
                'vendorDeliveryDate' => 5,
            ],

            [
                'stockQuantity' => 0,
                'expectedWeekCount' => 3,
                'preorder' => true,
                'transfer' => 10,
                'delivery' => 5,
                'vendorDeliveryDate' => 10,
            ],

            [
                'stockQuantity' => 0,
                'expectedWeekCount' => 4,
                'preorder' => true,
                'transfer' => 10,
                'delivery' => 11,
                'vendorDeliveryDate' => 11,
            ],

            [
                'stockQuantity' => 0,
                'expectedWeekCount' => 5,
                'preorder' => true,
                'transfer' => 10,
                'delivery' => 15,
                'vendorDeliveryDate' => 15,
            ],

            [
                'stockQuantity' => 0,
                'expectedWeekCount' => 6,
                'preorder' => true,
                'transfer' => 10,
                'delivery' => 18,
                'vendorDeliveryDate' => 18,
            ],

        ];
    }
}
