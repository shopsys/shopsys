<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\AvailabilityDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\UnitDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use ReflectionClass;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class ProductFacadeTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \App\Model\Product\ProductDataFactory
     * @inject
     */
    private $productDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     * @inject
     */
    private $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler
     * @inject
     */
    private $productPriceRecalculationScheduler;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     * @inject
     */
    private $vatFacade;

    /**
     * @dataProvider getTestCalculationHiddenAndSellingDeniedDataProvider
     * @param mixed $hidden
     * @param mixed $sellingDenied
     * @param mixed $calculatedHidden
     * @param mixed $calculatedSellingDenied
     */
    public function testCalculationHiddenAndSellingDenied(
        $hidden,
        $sellingDenied,
        $calculatedHidden,
        $calculatedSellingDenied
    ) {
        $productData = $this->productDataFactory->create();
        $productData->hidden = $hidden;
        $productData->sellingDenied = $sellingDenied;
        $productData->availability = $this->getReference(AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES);
        $productData->highPriceWithVat = [1 => Money::zero(), 2 => Money::zero()];
        $productData->preorder = true;
        $productData->catnum = '123';
        $this->setVats($productData);

        $product = $this->productFacade->create($productData);

        $this->em->clear();

        $productFromDb = $this->productFacade->getById($product->getId());

        $this->assertSame($calculatedHidden, $productFromDb->getCalculatedHidden());
        $this->assertSame($calculatedSellingDenied, $productFromDb->getCalculatedSellingDenied());
    }

    public function getTestCalculationHiddenAndSellingDeniedDataProvider()
    {
        return [
            [
                'hidden' => true,
                'sellingDenied' => true,
                'calculatedHidden' => true,
                'calculatedSellingDenied' => true,
            ],
            [
                'hidden' => false,
                'sellingDenied' => false,
                'calculatedHidden' => false,
                'calculatedSellingDenied' => false,
            ],
            [
                'hidden' => true,
                'sellingDenied' => false,
                'calculatedHidden' => true,
                'calculatedSellingDenied' => false,
            ],
            [
                'hidden' => false,
                'sellingDenied' => true,
                'calculatedHidden' => false,
                'calculatedSellingDenied' => true,
            ],
        ];
    }

    public function testEditMarkProductForVisibilityRecalculation()
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        $reflectionClass = new ReflectionClass(Product::class);
        $reflectionPropertyRecalculateVisibility = $reflectionClass->getProperty('recalculateVisibility');
        $reflectionPropertyRecalculateVisibility->setAccessible(true);
        $reflectionPropertyRecalculateVisibility->setValue($product, false);

        $this->productFacade->edit($product->getId(), $this->productDataFactory->createFromProduct($product));

        $this->assertSame(true, $reflectionPropertyRecalculateVisibility->getValue($product));
    }

    public function testEditSchedulesPriceRecalculation()
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $productId = $product->getId();

        $productData = $this->productDataFactory->create();
        $productData->highPriceWithVat = [1 => Money::zero(), 2 => Money::zero()];
        $productData->catnum = '123';
        $this->setVats($productData);

        $this->productFacade->edit($productId, $productData);

        $this->assertArrayHasKey($productId, $this->productPriceRecalculationScheduler->getProductsForImmediateRecalculation());
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
