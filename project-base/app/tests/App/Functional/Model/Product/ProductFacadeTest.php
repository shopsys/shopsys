<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\AvailabilityDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\StocksDataFixture;
use App\DataFixtures\Demo\UnitDataFixture;
use App\Model\Product\ProductData;
use App\Model\Product\ProductDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Stock\ProductStockDataFactory;
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
    private ProductPriceRecalculationScheduler $productPriceRecalculationScheduler;

    /**
     * @inject
     */
    private VatFacade $vatFacade;

    /**
     * @inject
     */
    private ProductStockDataFactory $productStockDataFactory;

    /**
     * @dataProvider getTestSellingDeniedDataProvider
     * @param mixed $hidden
     * @param mixed $sellingDenied
     * @param mixed $calculatedSellingDenied
     */
    public function testSellingDenied(
        $hidden,
        $sellingDenied,
        $calculatedSellingDenied,
    ) {
        $productData = $this->productDataFactory->create();
        $productData->hidden = $hidden;
        $productData->sellingDenied = $sellingDenied;
        $productData->availability = $this->getReference(AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES);

        /** @var \Shopsys\FrameworkBundle\Model\Stock\Stock $stock */
        $stock = $this->getReference(StocksDataFixture::STOCK_PREFIX . 1);

        $productStockData = $this->productStockDataFactory->createFromStock($stock);
        $productStockData->productQuantity = 10;
        $productData->stockProductData[$stock->getId()] = $productStockData;

        $productData->catnum = '123';
        $this->setVats($productData);

        $product = $this->productFacade->create($productData);

        $this->em->clear();

        $productFromDb = $this->productFacade->getById($product->getId());

        $this->assertSame($calculatedSellingDenied, $productFromDb->getCalculatedSellingDenied(), 'Calculated selling denied:');
    }

    public function getTestSellingDeniedDataProvider()
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

    public function testEditSchedulesPriceRecalculation()
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $productId = $product->getId();

        $productData = $this->productDataFactory->create();
        $productData->catnum = '123';
        $this->setVats($productData);

        $this->productFacade->edit($productId, $productData);

        $this->assertArrayHasKey(
            $productId,
            $this->productPriceRecalculationScheduler->getProductsForImmediateRecalculation(),
        );
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
