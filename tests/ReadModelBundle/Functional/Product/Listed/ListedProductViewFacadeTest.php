<?php

declare(strict_types=1);

namespace Tests\ReadModelBundle\Functional\Product\Listed;

use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\ReadModelBundle\Product\Listed\ListedProductView;
use Tests\App\Test\FunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class ListedProductViewFacadeTest extends FunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFacadeInterface
     * @inject
     */
    private $listedProductViewFacade;

    public function testGetAllAccessories(): void
    {
        $firstDomainLocale = $this->getFirstDomainLocale();

        $productId1 = 24;
        $productId2 = 13;

        $listedProductViews = $this->listedProductViewFacade->getAllAccessories(1);

        $this->assertCount(2, $listedProductViews);

        $this->assertArrayHasKey($productId1, $listedProductViews);
        $this->assertArrayHasKey($productId2, $listedProductViews);

        $this->assertInstanceOf(ListedProductView::class, $listedProductViews[$productId1]);
        $this->assertInstanceOf(ListedProductView::class, $listedProductViews[$productId2]);

        $this->assertEquals(t('Kabel HDMI A - HDMI A M/M 2m gold-plated connectors High Speed HD', [], 'dataFixtures', $firstDomainLocale), $listedProductViews[$productId1]->getName());
        $this->assertEquals(t('Defender 2.0 SPK-480', [], 'dataFixtures', $firstDomainLocale), $listedProductViews[$productId2]->getName());
    }

    public function testGetPaginatedForBrand(): void
    {
        $brandId = 1;
        $foundProductId = 5;

        $paginationResults = $this->listedProductViewFacade->getPaginatedForBrand($brandId, ProductListOrderingConfig::ORDER_BY_NAME_ASC, 1, 10);
        $listedProductViews = $paginationResults->getResults();

        $this->assertCount(1, $listedProductViews);
        $this->assertArrayHasKey($foundProductId, $listedProductViews);
        $this->assertInstanceOf(ListedProductView::class, $listedProductViews[$foundProductId]);
    }

    public function testGetFilteredPaginatedForSearch(): void
    {
        $firstDomainLocale = $this->getFirstDomainLocale();
        $emptyFilterData = new ProductFilterData();

        $paginationResults = $this->listedProductViewFacade->getFilteredPaginatedForSearch('kitty', $emptyFilterData, ProductListOrderingConfig::ORDER_BY_NAME_ASC, 1, 10);
        $listedProductViews = $paginationResults->getResults();

        $this->assertArrayHasKey(1, $listedProductViews);
        $this->assertInstanceOf(ListedProductView::class, $listedProductViews[1]);
        $this->assertEquals(t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], 'dataFixtures', $firstDomainLocale), $listedProductViews[1]->getName());
    }

    public function testGetTop(): void
    {
        $firstDomainLocale = $this->getFirstDomainLocale();

        $firstTopProductId = 1;

        $listedProductViews = $this->listedProductViewFacade->getTop(1);

        $this->assertCount(1, $listedProductViews);
        $this->assertArrayHasKey($firstTopProductId, $listedProductViews);
        $this->assertInstanceOf(ListedProductView::class, $listedProductViews[$firstTopProductId]);
        $this->assertEquals(t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], 'dataFixtures', $firstDomainLocale), $listedProductViews[$firstTopProductId]->getName());
    }

    public function testGetFilteredPaginatedInCategory(): void
    {
        $emptyFilterData = new ProductFilterData();

        $categoryId = 9;

        $paginationResults = $this->listedProductViewFacade->getFilteredPaginatedInCategory($categoryId, $emptyFilterData, ProductListOrderingConfig::ORDER_BY_NAME_ASC, 1, 5);
        $listedProductViews = $paginationResults->getResults();

        $this->assertCount(5, $listedProductViews);
        $this->assertContainsOnlyInstancesOf(ListedProductView::class, $listedProductViews);
    }
}
