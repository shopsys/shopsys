<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Availability;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use DateTimeImmutable;
use IntlDateFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatterInterface;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityStatusEnum;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Stock\ProductStockDataFactory;
use Shopsys\FrameworkBundle\Model\Stock\StockFacade;
use Shopsys\FrameworkBundle\Model\Stock\StockSettingsData;
use Shopsys\FrameworkBundle\Model\Stock\StockSettingsDataFacade;
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
    private ProductDataFactory $productDataFactory;

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

    /**
     * @inject
     */
    private DateTimeFormatterInterface $dateTimeFormatter;

    /**
     * @inject
     */
    private DisplayTimeZoneProviderInterface $displayTimeZoneProvider;

    #[DataProvider('getTestIsProductAvailableOnDomainProvider')]
    public function testIsProductAvailableOnDomain(int $stockQuantity, bool $expectedIsProductAvailableOnDomain): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->createFromProduct($product);

        $productData->productStockData = [];

        foreach ($this->stockFacade->getStocksEnabledOnDomainIndexedByStockId(self::FIRST_DOMAIN_ID) as $stock) {
            $productStockData = $this->productStockDataFactory->createFromStock($stock);
            $productStockData->productQuantity = $stockQuantity;

            $productData->productStockData[] = $productStockData;
        }

        $this->productFacade->edit($product->getId(), $productData);

        $this->em->refresh($product);

        $this->assertSame($expectedIsProductAvailableOnDomain, $this->productAvailabilityFacade->isProductAvailableOnDomainCached($product, self::FIRST_DOMAIN_ID));
    }

    public static function getTestIsProductAvailableOnDomainProvider(): array
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

    public function testGroupedStockQuantity(): void
    {
        $stockQuantity = 5;
        $expected = count($this->stockFacade->getStocksEnabledOnDomainIndexedByStockId(self::FIRST_DOMAIN_ID)) * $stockQuantity;

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->createFromProduct($product);

        $productData->productStockData = [];

        foreach ($this->stockFacade->getStocksEnabledOnDomainIndexedByStockId(self::FIRST_DOMAIN_ID) as $stock) {
            $productStockData = $this->productStockDataFactory->createFromStock($stock);
            $productStockData->productQuantity = $stockQuantity;
            $productData->productStockData[] = $productStockData;
        }

        $this->productFacade->edit($product->getId(), $productData);

        $this->em->refresh($product);

        $this->assertSame($expected, $this->productAvailabilityFacade->getGroupedStockQuantityByProductAndDomainId($product, self::FIRST_DOMAIN_ID));
    }

    /**
     * @param int<-1,0> $expectedWeekCount
     */
    #[DataProvider('getTestProductAvailabilityInformationByDomainIdProvider')]
    public function testProductAvailabilityInformationByDomainId(
        int $stockQuantity,
        int $expectedWeekCount,
        int $transfer,
    ): void {
        $stockSettingsData = new StockSettingsData();
        $stockSettingsData->transfer = $transfer;
        $this->stockSettingsDataFacade->edit(
            $stockSettingsData,
            $this->domain->getDomainConfigById(self::FIRST_DOMAIN_ID),
        );

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->createFromProduct($product);

        $productData->productStockData = [];

        foreach ($this->stockFacade->getStocksEnabledOnDomainIndexedByStockId(self::FIRST_DOMAIN_ID) as $stock) {
            $productStockData = $this->productStockDataFactory->createFromStock($stock);
            $productStockData->productQuantity = $stockQuantity;
            $productData->productStockData[] = $productStockData;
        }

        $this->productFacade->edit($product->getId(), $productData);

        $this->em->refresh($product);

        $expected = match ($expectedWeekCount) {
            -1 => t('Out of stock', [], Translator::CUSTOMER_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            0 => t('In stock', [], Translator::CUSTOMER_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
        };

        $this->assertSame($expected, $this->productAvailabilityFacade->getProductAvailabilityInfoByProduct($product, self::FIRST_DOMAIN_ID)->name);
    }

    public static function getTestProductAvailabilityInformationByDomainIdProvider(): array
    {
        return [
            [
                'stockQuantity' => 5,
                'expectedWeekCount' => 0,
                'transfer' => 10,
            ],
            [
                'stockQuantity' => 0,
                'expectedWeekCount' => -1,
                'transfer' => 10,
            ],
        ];
    }

    public function testAvailabilityIsExpectedRestockWhenOutOfStockWithFutureRestockingDate(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '10', Product::class);
        $expectedRestockingDate = $product->getExpectedRestockingDate();

        $this->assertNotNull($expectedRestockingDate);
        $this->assertEquals(
            $expectedRestockingDate,
            $this->productAvailabilityFacade->findEffectiveExpectedRestockingDate($product, self::FIRST_DOMAIN_ID),
        );
        $availability = $this->productAvailabilityFacade->getProductAvailabilityInfoByProduct($product, self::FIRST_DOMAIN_ID);
        $this->assertSame(AvailabilityStatusEnum::EXPECTED_RESTOCK, $availability->status);
        $this->assertSame($this->getExpectedRestockTextForDate($expectedRestockingDate), $availability->name);
    }

    public function testAvailabilityStaysOutOfStockWhenRestockingDateHasPassed(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '21', Product::class);

        $this->assertNotNull($product->getExpectedRestockingDate());
        $this->assertNull(
            $this->productAvailabilityFacade->findValidExpectedRestockingDate($product, self::FIRST_DOMAIN_ID),
        );
        $this->assertNull(
            $this->productAvailabilityFacade->findEffectiveExpectedRestockingDate($product, self::FIRST_DOMAIN_ID),
        );
        $availability = $this->productAvailabilityFacade->getProductAvailabilityInfoByProduct($product, self::FIRST_DOMAIN_ID);
        $this->assertSame(AvailabilityStatusEnum::OUT_OF_STOCK, $availability->status);
        $this->assertSame(
            t('Out of stock', [], Translator::CUSTOMER_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            $availability->name,
        );
    }

    public function testRestockingDateIsIgnoredForAvailabilityWhenProductIsInStock(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '4', Product::class);
        $expectedRestockingDate = $product->getExpectedRestockingDate();

        $this->assertNotNull($expectedRestockingDate);
        $this->assertEquals(
            $expectedRestockingDate,
            $this->productAvailabilityFacade->findValidExpectedRestockingDate($product, self::FIRST_DOMAIN_ID),
        );
        $this->assertNull(
            $this->productAvailabilityFacade->findEffectiveExpectedRestockingDate($product, self::FIRST_DOMAIN_ID),
        );
        $this->assertSame(AvailabilityStatusEnum::IN_STOCK, $this->productAvailabilityFacade->getProductAvailabilityInfoByProduct($product, self::FIRST_DOMAIN_ID)->status);
    }

    public function testMainVariantStockQuantityIsNull(): void
    {
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '83', Product::class);

        $this->assertNull($this->productAvailabilityFacade->getGroupedStockQuantityByProductAndDomainId($mainVariant, self::FIRST_DOMAIN_ID));
    }

    public function testMainVariantStoresAvailabilitiesIsEmpty(): void
    {
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '83', Product::class);

        $this->assertEmpty($this->productAvailabilityFacade->getProductStoresAvailabilitiesInformationByDomainIdIndexedByStoreId($mainVariant, self::FIRST_DOMAIN_ID));
    }

    public function testMainVariantAvailableStoresCountIsNull(): void
    {
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '83', Product::class);

        $this->assertNull($this->productAvailabilityFacade->getAvailableStoresCount($mainVariant, self::FIRST_DOMAIN_ID));
    }

    public function testMainVariantAvailableOnDomain(): void
    {
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '83', Product::class);

        $this->assertTrue($this->productAvailabilityFacade->isProductAvailableOnDomainCached($mainVariant, self::FIRST_DOMAIN_ID));
    }

    public function testMainVariantIsNotAvailableWhenNoVariantIsAvailable(): void
    {
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '83', Product::class);
        $onlyAvailableVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '79', Product::class);
        $this->setProductOutOfStock($onlyAvailableVariant);

        $this->assertFalse($this->productAvailabilityFacade->isProductAvailableOnDomainCached($mainVariant, self::FIRST_DOMAIN_ID));
    }

    private function getExpectedRestockTextForDate(DateTimeImmutable $expectedRestockingDate): string
    {
        $formattedDate = (string)$this->dateTimeFormatter->format(
            $expectedRestockingDate->setTimezone(
                $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId(self::FIRST_DOMAIN_ID),
            ),
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::NONE,
            $this->getFirstDomainLocale(),
        );

        return t('Expecting %date%', ['%date%' => $formattedDate], Translator::CUSTOMER_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
    }

    private function setProductOutOfStock(Product $onlyAvailableVariant): void
    {
        $productData = $this->productDataFactory->createFromProduct($onlyAvailableVariant);

        foreach ($productData->productStockData as $productStockData) {
            $productStockData->productQuantity = 0;
        }
        $this->productFacade->edit($onlyAvailableVariant->getId(), $productData);
        $this->handleDispatchedRecalculationMessages();
    }
}
