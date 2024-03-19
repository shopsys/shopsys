<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Tests\App\Test\TransactionFunctionalTestCase;

class ProductRepositoryTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ProductRepository $productRepository;

    public function testVisibleAndNotSellingDeniedProductIsListed()
    {
        $this->getAllListableQueryBuilderTest(1, true);
    }

    public function testVisibleAndSellingDeniedProductIsNotListed()
    {
        $this->getAllListableQueryBuilderTest(6, false);
    }

    public function testProductVariantIsNotListed()
    {
        $this->getAllListableQueryBuilderTest(53, false);
    }

    public function testProductMainVariantIsListed()
    {
        $this->getAllListableQueryBuilderTest(69, true);
    }

    /**
     * @param mixed $productReferenceId
     * @param mixed $isExpectedInResult
     */
    private function getAllListableQueryBuilderTest($productReferenceId, $isExpectedInResult)
    {
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
            PricingGroup::class,
        );

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productReferenceId, Product::class);
        $productId = $product->getId();

        $queryBuilder = $this->productRepository->getAllListableQueryBuilder($this->domain->getId(), $pricingGroup);
        $queryBuilder->andWhere('p.id = :id')
            ->setParameter('id', $productId);
        $result = $queryBuilder->getQuery()->execute();

        $this->assertSame(in_array($product, $result, true), $isExpectedInResult);
    }

    public function testVisibleAndNotSellingDeniedProductIsSellable()
    {
        $this->getAllSellableQueryBuilderTest(1, true);
    }

    public function testVisibleAndSellingDeniedProductIsNotSellable()
    {
        $this->getAllSellableQueryBuilderTest(6, false);
    }

    public function testProductVariantIsSellable()
    {
        $this->getAllSellableQueryBuilderTest(53, true);
    }

    public function testProductMainVariantIsNotSellable()
    {
        $this->getAllSellableQueryBuilderTest(69, false);
    }

    /**
     * @param mixed $productReferenceId
     * @param mixed $isExpectedInResult
     */
    private function getAllSellableQueryBuilderTest($productReferenceId, $isExpectedInResult)
    {
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
            PricingGroup::class,
        );

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productReferenceId, Product::class);
        $productId = $product->getId();

        $queryBuilder = $this->productRepository->getAllSellableQueryBuilder($this->domain->getId(), $pricingGroup);
        $queryBuilder->andWhere('p.id = :id')
            ->setParameter('id', $productId);
        $result = $queryBuilder->getQuery()->execute();

        $this->assertSame(in_array($product, $result, true), $isExpectedInResult);
    }

    public function testVisibleAndNotSellingDeniedProductIsOfferred()
    {
        $this->getAllOfferedQueryBuilderTest(1, true);
    }

    public function testVisibleAndSellingDeniedProductIsNotOfferred()
    {
        $this->getAllOfferedQueryBuilderTest(6, false);
    }

    public function testProductVariantIsOfferred()
    {
        $this->getAllOfferedQueryBuilderTest(53, true);
    }

    public function testProductMainVariantIsOfferred()
    {
        $this->getAllOfferedQueryBuilderTest(69, true);
    }

    /**
     * @param mixed $productReferenceId
     * @param mixed $isExpectedInResult
     */
    private function getAllOfferedQueryBuilderTest($productReferenceId, $isExpectedInResult)
    {
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
            PricingGroup::class,
        );

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productReferenceId, Product::class);
        $productId = $product->getId();

        $queryBuilder = $this->productRepository->getAllOfferedQueryBuilder($this->domain->getId(), $pricingGroup);
        $queryBuilder->andWhere('p.id = :id')
            ->setParameter('id', $productId);
        $result = $queryBuilder->getQuery()->execute();

        $this->assertSame(in_array($product, $result, true), $isExpectedInResult);
    }

    public function testGetSortedProductsByIds()
    {
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
            PricingGroup::class,
        );

        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);
        $product2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 2, Product::class);
        $product3 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 3, Product::class);
        $product4 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 4, Product::class);

        $sortedProducts = [
            $product4,
            $product1,
            $product3,
            $product2,
        ];

        $sortedProductIds = [
            $product4->getId(),
            $product1->getId(),
            $product3->getId(),
            $product2->getId(),
        ];

        $results = $this->productRepository->getOfferedByIds($this->domain->getId(), $pricingGroup, $sortedProductIds);

        $this->assertSame($sortedProducts, $results);
    }
}
