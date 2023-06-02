<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Comparison;

use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Comparison\ComparisonFacade;
use App\Model\Product\Comparison\ComparisonRepository;
use Tests\App\Test\TransactionFunctionalTestCase;

class ComparisonFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     * @var \App\Model\Product\Comparison\ComparisonFacade
     */
    private ComparisonFacade $comparisonFacade;

    /**
     * @inject
     * @var \App\Model\Product\Comparison\ComparisonRepository
     */
    private ComparisonRepository $comparisonRepository;

    public function testAddProductToNotExistingComparison(): void
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . 1);

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);

        $comparison = $this->comparisonFacade->addProductToComparison($product, $customerUser, null);
        $this->assertSame(1, $comparison->getItemsCount());
    }

    public function testRemoveLastProductFromComparison(): void
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . 1);

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);

        $comparison = $this->comparisonFacade->addProductToComparison($product, $customerUser, null);
        $this->assertSame(1, $comparison->getItemsCount());

        $returnedComparison = $this->comparisonFacade->removeProductFromComparison($product, $customerUser, null);
        $this->assertNull($returnedComparison);
    }

    public function testCleanComparison(): void
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . 1);

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);

        $comparison = $this->comparisonFacade->addProductToComparison($product, $customerUser, null);
        $this->assertSame(1, $comparison->getItemsCount());

        $comparisonId = $comparison->getId();
        $this->comparisonFacade->cleanComparison($customerUser, null);

        $actualComparison = $this->comparisonRepository->findById($comparisonId);
        $this->assertNull($actualComparison);
    }
}
