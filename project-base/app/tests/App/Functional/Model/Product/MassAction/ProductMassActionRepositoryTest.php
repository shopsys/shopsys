<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\MassAction;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\MassAction\ProductMassActionRepository;
use Tests\App\Test\TableRowTestHelper;
use Tests\App\Test\TransactionFunctionalTestCase;

final class ProductMassActionRepositoryTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ProductMassActionRepository $productMassActionRepository;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private TableRowTestHelper $tableRowTestHelper;

    public function testSetHiddenHidesSelectedProducts(): void
    {
        $selectedProductIds = $this->getSelectedProductIds();
        $this->productMassActionRepository->setHidden($selectedProductIds, true);
        $this->em->clear();

        foreach ($selectedProductIds as $productId) {
            $this->assertTrue($this->productFacade->getById($productId)->isHidden());
        }
    }

    public function testSetHiddenDoesNotRewriteProductsWithUnchangedHiddenFlag(): void
    {
        $rowVersionsBeforeMassAction = $this->getProductRowVersions();

        $this->productMassActionRepository->setHidden($this->getSelectedProductIds(), false);

        $this->assertSame($rowVersionsBeforeMassAction, $this->getProductRowVersions());
    }

    /**
     * @return int[]
     */
    private function getSelectedProductIds(): array
    {
        return array_map(
            fn (int $productNumber): int => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productNumber, Product::class)->getId(),
            [1, 2, 3],
        );
    }

    /**
     * @return array<string, string>
     */
    private function getProductRowVersions(): array
    {
        return $this->tableRowTestHelper->getRowVersionsIndexedByPrimaryKey('products', ['id']);
    }
}
