<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

class ProductVisibilityFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ProductVisibilityFacade $productVisibilityFacade;

    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    public function testAreProductsVisibleForDefaultPricingGroupOnSomeDomainIndexedByProductId(): void
    {
        $productId = 1;
        $visibilityIndexedByProductId = $this->productVisibilityFacade->areProductsVisibleForDefaultPricingGroupOnSomeDomainIndexedByProductId(
            [$productId],
        );
        $this->assertTrue($visibilityIndexedByProductId[$productId]);
    }

    public function testAreProductsVisibleForDefaultPricingGroupOnEachDomainIndexedByProductId(): void
    {
        $productId = 1;

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productId);
        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->domainHidden[Domain::FIRST_DOMAIN_ID] = true;

        $this->productFacade->edit($productId, $productData);
        $this->handleDispatchedRecalculationMessages();

        $visibilityIndexedByProductId = $this->productVisibilityFacade->areProductsVisibleForDefaultPricingGroupOnEachDomainIndexedByProductId(
            [$productId],
        );
        $this->assertFalse($visibilityIndexedByProductId[$productId], 'Product was hidden on first domain, should not be visible on all domains');

        $visibilityIndexedByProductId = $this->productVisibilityFacade->areProductsVisibleForDefaultPricingGroupOnSomeDomainIndexedByProductId(
            [$productId],
        );
        $this->assertSame($visibilityIndexedByProductId[$productId], $this->domain->isMultidomain());
    }
}
