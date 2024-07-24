<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\StocksDataFixture;
use App\DataFixtures\Demo\UnitDataFixture;
use App\Model\Product\ProductDataFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\Unit\Unit;
use Shopsys\FrameworkBundle\Model\Stock\ProductStockDataFactory;
use Shopsys\FrameworkBundle\Model\Stock\Stock;
use Tests\App\Test\TransactionFunctionalTestCase;

class ProductFacadeTest extends TransactionFunctionalTestCase
{
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
     * @param bool $hidden
     * @param bool $sellingDenied
     * @param bool $calculatedSellingDenied
     */
    #[DataProvider('getTestSellingDeniedDataProvider')]
    public function testSellingDenied(
        bool $hidden,
        bool $sellingDenied,
        bool $calculatedSellingDenied,
    ): void {
        $productData = $this->productDataFactory->create();
        $productData->hidden = $hidden;
        $productData->sellingDenied = $sellingDenied;
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES, Unit::class);

        $stock = $this->getReference(StocksDataFixture::STOCK_PREFIX . 1, Stock::class);

        $productStockData = $this->productStockDataFactory->createFromStock($stock);
        $productStockData->productQuantity = 10;
        $productData->productStockData[$stock->getId()] = $productStockData;

        $productData->catnum = '123';

        $product = $this->productFacade->create($productData);

        $this->handleDispatchedRecalculationMessages();

        $this->em->clear();

        $productFromDb = $this->productFacade->getById($product->getId());

        $this->assertSame($calculatedSellingDenied, $productFromDb->getCalculatedSellingDenied(), 'Calculated selling denied:');
    }

    /**
     * @return array
     */
    public static function getTestSellingDeniedDataProvider(): array
    {
        return [
            [
                'hidden' => true,
                'sellingDenied' => true,
                'calculatedSellingDenied' => true,
            ],
            [
                'hidden' => false,
                'sellingDenied' => false,
                'calculatedSellingDenied' => false,
            ],
            [
                'hidden' => true,
                'sellingDenied' => false,
                'calculatedSellingDenied' => false,
            ],
            [
                'hidden' => false,
                'sellingDenied' => true,
                'calculatedSellingDenied' => true,
            ],
        ];
    }
}
