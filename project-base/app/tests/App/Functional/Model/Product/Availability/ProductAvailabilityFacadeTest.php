<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Availability;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface;
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

    /**
     * @dataProvider getTestIsProductAvailableOnDomainProvider
     * @param int $stockQuantity
     * @param bool $expectedIsProductAvailableOnDomain
     */
    public function testIsProductAvailableOnDomain(int $stockQuantity, bool $expectedIsProductAvailableOnDomain)
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

    public function testGroupedStockQuantity()
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
     * @dataProvider getTestProductAvailabilityInformationByDomainIdProvider
     * @param int $stockQuantity
     * @param int<-1,0> $expectedWeekCount
     * @param int $transfer
     */
    public function testProductAvailabilityInformationByDomainId(
        int $stockQuantity,
        int $expectedWeekCount,
        int $transfer,
    ): void {
        $stockSettingsData = new StockSettingsData();
        $stockSettingsData->transfer = $transfer;
        $this->stockSettingsDataFacade->edit($stockSettingsData);

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
            -1 => t('Out of stock', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            0 => t('In stock', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
        };

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
}
