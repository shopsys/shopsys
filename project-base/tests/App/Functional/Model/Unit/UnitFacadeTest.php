<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Unit;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\UnitDataFixture;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitData;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class UnitFacadeTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @inject
     */
    private UnitFacade $unitFacade;

    /**
     * @inject
     */
    private ProductDataFactoryInterface $productDataFactory;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    public function testDeleteByIdAndReplace()
    {
        $unitData = new UnitData();
        $unitData->name = ['cs' => 'name'];
        $unitToDelete = $this->unitFacade->create($unitData);
        /** @var \Shopsys\FrameworkBundle\Model\Product\Unit\Unit $unitToReplaceWith */
        $unitToReplaceWith = $this->getReference(UnitDataFixture::UNIT_PIECES);
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        $productData = $this->productDataFactory->createFromProduct($product);

        $productData->unit = $unitToDelete;
        $this->productFacade->edit($product->getId(), $productData);

        $this->unitFacade->deleteById($unitToDelete->getId(), $unitToReplaceWith->getId());

        $this->em->refresh($product);

        $this->assertEquals($unitToReplaceWith, $product->getUnit());
    }
}
