<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\StocksDataFixture;
use App\DataFixtures\Demo\UnitDataFixture;
use App\Model\Product\ProductData;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\Unit\Unit;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
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
    private VatFacade $vatFacade;

    /**
     * @inject
     */
    private ProductStockDataFactory $productStockDataFactory;

    /**
     * @param mixed $hidden
     * @param mixed $sellingDenied
     * @param mixed $calculatedSellingDenied
     */
    #[DataProvider('getTestSellingDeniedDataProvider')]
    public function testSellingDenied(
        $hidden,
        $sellingDenied,
        $calculatedSellingDenied,
    ) {
        $productData = $this->productDataFactory->create();
        $productData->hidden = $hidden;
        $productData->sellingDenied = $sellingDenied;
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES, Unit::class);

        $stock = $this->getReference(StocksDataFixture::STOCK_PREFIX . 1, Stock::class);

        $productStockData = $this->productStockDataFactory->createFromStock($stock);
        $productStockData->productQuantity = 10;
        $productData->productStockData[$stock->getId()] = $productStockData;

        $productData->catnum = '123';
        $this->setVats($productData);

        $product = $this->productFacade->create($productData);

        $this->handleDispatchedRecalculationMessages();

        $this->em->clear();

        $productFromDb = $this->productFacade->getById($product->getId());

        $this->assertSame($calculatedSellingDenied, $productFromDb->getCalculatedSellingDenied(), 'Calculated selling denied:');
    }

    public static function getTestSellingDeniedDataProvider()
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

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    private function setVats(ProductData $productData): void
    {
        $productVatsIndexedByDomainId = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $productVatsIndexedByDomainId[$domainId] = $this->vatFacade->getDefaultVatForDomain($domainId);
        }
        $productData->vatsIndexedByDomainId = $productVatsIndexedByDomainId;
    }
}
