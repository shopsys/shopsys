<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\StocksDataFixture;
use App\DataFixtures\Demo\UnitDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Category\Category;
use App\Model\Product\ProductDataFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductInputPriceDataFactory;
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
     * @inject
     */
    private ProductInputPriceDataFactory $productInputPriceDataFactory;

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
        $productData->productInputPricesByDomain[Domain::FIRST_DOMAIN_ID] = $this->productInputPriceDataFactory->create(
            $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, Domain::FIRST_DOMAIN_ID, Vat::class),
            [Domain::FIRST_DOMAIN_ID => Money::create(1)],
        );

        $productData->name[$this->getFirstDomainLocale()] = 'Test product';
        $productData->categoriesByDomainId[Domain::FIRST_DOMAIN_ID][] = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);

        $stock = $this->getReference(StocksDataFixture::STOCK_PREFIX . 1, Stock::class);

        $productStockData = $this->productStockDataFactory->createFromStock($stock);
        $productStockData->productQuantity = 10;
        $productData->productStockData[$stock->getId()] = $productStockData;

        $productData->catnum = '123';

        $product = $this->productFacade->create($productData);

        $this->handleDispatchedRecalculationMessages();

        $this->em->clear();

        $productFromDb = $this->productFacade->getById($product->getId());

        $this->assertSame($calculatedSellingDenied, $productFromDb->isCalculatedSellingDenied(Domain::FIRST_DOMAIN_ID), 'Calculated selling denied:');
    }

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
                'sellingDenied' => true,
                'calculatedSellingDenied' => true,
            ],
            [
                'hidden' => false,
                'sellingDenied' => true,
                'calculatedSellingDenied' => true,
            ],
        ];
    }
}
