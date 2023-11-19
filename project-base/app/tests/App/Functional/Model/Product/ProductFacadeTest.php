<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\AvailabilityDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\UnitDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use App\Model\Product\ProductDataFactory;
use ReflectionClass;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
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
     * @dataProvider getTestCalculationHiddenAndSellingDeniedDataProvider
     * @param mixed $hidden
     * @param mixed $sellingDenied
     * @param mixed $calculatedHidden
     * @param mixed $calculatedSellingDenied
     */
    public function testCalculationHiddenAndSellingDenied(
        bool $hidden,
        bool $sellingDenied,
        bool $calculatedHidden,
        bool $calculatedSellingDenied,
    ): void {
        $productData = $this->productDataFactory->create();
        $productData->hidden = $hidden;
        $productData->sellingDenied = $sellingDenied;
        $productData->availability = $this->getReference(AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES);
        $productData->preorder = true;
        $productData->catnum = '123';
        $this->setVats($productData);

        $product = $this->productFacade->create($productData);

        $this->em->clear();

        $productFromDb = $this->productFacade->getById($product->getId());

        $this->assertSame($calculatedHidden, $productFromDb->getCalculatedHidden());
        $this->assertSame($calculatedSellingDenied, $productFromDb->getCalculatedSellingDenied());
    }

    /**
     * @return array<int, array<'calculatedHidden'|'calculatedSellingDenied'|'hidden'|'sellingDenied', bool>>
     */
    public function getTestCalculationHiddenAndSellingDeniedDataProvider(): array
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

    public function testEditMarkProductForVisibilityRecalculation(): void
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

    public function testEditSchedulesPriceRecalculation(): void
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
