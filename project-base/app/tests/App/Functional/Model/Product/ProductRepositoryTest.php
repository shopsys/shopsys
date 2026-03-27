<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Tests\App\Test\TransactionFunctionalTestCase;

class ProductRepositoryTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ProductRepository $productRepository;

    public function testVisibleAndNotSellingDeniedProductIsListed(): void
    {
        $this->getAllListableQueryBuilderTest(1, true);
    }

    public function testVisibleAndSellingDeniedProductIsNotListed(): void
    {
        $this->getAllListableQueryBuilderTest(6, false);
    }

    public function testProductVariantIsNotListed(): void
    {
        $this->getAllListableQueryBuilderTest(53, false);
    }

    public function testProductMainVariantIsListed(): void
    {
        $this->getAllListableQueryBuilderTest(69, true);
    }

    private function getAllListableQueryBuilderTest(int $productReferenceId, bool $isExpectedInResult): void
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
        $result = $queryBuilder->getQuery()->getResult();

        $this->assertSame(in_array($product, $result, true), $isExpectedInResult);
    }

    public function testVisibleAndNotSellingDeniedProductIsSellable(): void
    {
        $this->getAllSellableQueryBuilderTest(1, true);
    }

    public function testVisibleAndSellingDeniedProductIsNotSellable(): void
    {
        $this->getAllSellableQueryBuilderTest(6, false);
    }

    public function testProductVariantIsSellable(): void
    {
        $this->getAllSellableQueryBuilderTest(53, true);
    }

    public function testProductMainVariantIsNotSellable(): void
    {
        $this->getAllSellableQueryBuilderTest(69, false);
    }

    private function getAllSellableQueryBuilderTest(int $productReferenceId, bool $isExpectedInResult): void
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
        $result = $queryBuilder->getQuery()->getResult();

        $this->assertSame(in_array($product, $result, true), $isExpectedInResult);
    }

    public function testVisibleAndNotSellingDeniedProductIsOffered(): void
    {
        $this->getAllOfferedQueryBuilderTest(1, true);
    }

    public function testVisibleAndSellingDeniedProductIsNotOffered(): void
    {
        $this->getAllOfferedQueryBuilderTest(6, false);
    }

    public function testProductVariantIsOffered(): void
    {
        $this->getAllOfferedQueryBuilderTest(53, true);
    }

    public function testProductMainVariantIsOffered(): void
    {
        $this->getAllOfferedQueryBuilderTest(69, true);
    }

    private function getAllOfferedQueryBuilderTest(int $productReferenceId, bool $isExpectedInResult): void
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
        $result = $queryBuilder->getQuery()->getResult();

        $this->assertSame(in_array($product, $result, true), $isExpectedInResult);
    }

    public function testGetSortedProductsByIds(): void
    {
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
            PricingGroup::class,
        );

        $sortedProductIds = [4, 1, 3, 2];

        $results = $this->productRepository->getOfferedByIds($this->domain->getId(), $pricingGroup, $sortedProductIds);

        $this->assertSame(
            $sortedProductIds,
            array_map(
                static fn (Product $product) => $product->getId(),
                $results,
            ),
        );
    }
}
