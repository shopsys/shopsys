<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Category\Category;
use App\Model\Product\Filter\ProductFilterData;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Tests\App\Test\TransactionFunctionalTestCase;

class ProductRepositoryTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ProductRepository $productRepository;

    /**
     * @inject
     */
    private ProductDataFactoryInterface $productDataFactory;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

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

    /**
     * @param mixed $productReferenceId
     * @param mixed $isExpectedInResult
     */
    private function getAllListableQueryBuilderTest(int $productReferenceId, bool $isExpectedInResult): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
        );

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productReferenceId);
        $productId = $product->getId();

        $queryBuilder = $this->productRepository->getAllListableQueryBuilder($this->domain->getId(), $pricingGroup);
        $queryBuilder->andWhere('p.id = :id')
            ->setParameter('id', $productId);
        $result = $queryBuilder->getQuery()->execute();

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

    /**
     * @param mixed $productReferenceId
     * @param mixed $isExpectedInResult
     */
    private function getAllSellableQueryBuilderTest(int $productReferenceId, bool $isExpectedInResult): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
        );

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productReferenceId);
        $productId = $product->getId();

        $queryBuilder = $this->productRepository->getAllSellableQueryBuilder($this->domain->getId(), $pricingGroup);
        $queryBuilder->andWhere('p.id = :id')
            ->setParameter('id', $productId);
        $result = $queryBuilder->getQuery()->execute();

        $this->assertSame(in_array($product, $result, true), $isExpectedInResult);
    }

    public function testVisibleAndNotSellingDeniedProductIsOfferred(): void
    {
        $this->getAllOfferedQueryBuilderTest(1, true);
    }

    public function testVisibleAndSellingDeniedProductIsNotOfferred(): void
    {
        $this->getAllOfferedQueryBuilderTest(6, false);
    }

    public function testProductVariantIsOfferred(): void
    {
        $this->getAllOfferedQueryBuilderTest(53, true);
    }

    public function testProductMainVariantIsOfferred(): void
    {
        $this->getAllOfferedQueryBuilderTest(69, true);
    }

    /**
     * @param mixed $productReferenceId
     * @param mixed $isExpectedInResult
     */
    private function getAllOfferedQueryBuilderTest(int $productReferenceId, bool $isExpectedInResult): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
        );

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productReferenceId);
        $productId = $product->getId();

        $queryBuilder = $this->productRepository->getAllOfferedQueryBuilder($this->domain->getId(), $pricingGroup);
        $queryBuilder->andWhere('p.id = :id')
            ->setParameter('id', $productId);
        $result = $queryBuilder->getQuery()->execute();

        $this->assertSame(in_array($product, $result, true), $isExpectedInResult);
    }

    public function testOrderingByProductPriorityInCategory(): void
    {
        /** @var \App\Model\Category\Category $category */
        $category = $this->getReference(CategoryDataFixture::CATEGORY_FOOD);
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 70);
        $product2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 71);

        $this->setProductOrderingPriority($product1, 0);
        $this->setProductOrderingPriority($product2, -1);

        $results = $this->getProductsInCategoryOrderedByPriority($category);
        $this->assertSame($product1, $results[0]);
        $this->assertSame($product2, $results[1]);

        $this->setProductOrderingPriority($product2, 1);

        $results = $this->getProductsInCategoryOrderedByPriority($category);
        $this->assertSame($product2, $results[0]);
        $this->assertSame($product1, $results[1]);
    }

    public function testOrderingByProductPriorityInSearch(): void
    {
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $product2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 45);

        $this->setProductOrderingPriority($product1, 2);
        $this->setProductOrderingPriority($product2, 3);

        $results = $this->getProductsForSearchOrderedByPriority('sencor');
        $this->assertSame($product2, $results[0]);
        $this->assertSame($product1, $results[1]);

        $this->setProductOrderingPriority($product2, 1);

        $results = $this->getProductsForSearchOrderedByPriority('sencor');
        $this->assertSame($product1, $results[0]);
        $this->assertSame($product2, $results[1]);
    }

    public function testGetSortedProductsByIds(): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
        );

        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        /** @var \App\Model\Product\Product $product2 */
        $product2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 2);
        /** @var \App\Model\Product\Product $product3 */
        $product3 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 3);
        /** @var \App\Model\Product\Product $product4 */
        $product4 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 4);

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

    /**
     * @param \App\Model\Product\Product $product
     * @param int $priority
     */
    private function setProductOrderingPriority(Product $product, int $priority): void
    {
        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->orderingPriority = $priority;
        $this->productFacade->edit($product->getId(), $productData);
    }

    /**
     * @param string $searchText
     * @return \App\Model\Product\Product[]
     */
    private function getProductsForSearchOrderedByPriority(string $searchText): array
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
        );

        $paginationResult = $this->productRepository->getPaginationResultForSearchListable(
            $searchText,
            Domain::FIRST_DOMAIN_ID,
            $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale(),
            new ProductFilterData(),
            ProductListOrderingConfig::ORDER_BY_PRIORITY,
            $pricingGroup,
            1,
            PHP_INT_MAX,
        );

        return $paginationResult->getResults();
    }

    /**
     * @param \App\Model\Category\Category $category
     * @return \App\Model\Product\Product[]
     */
    private function getProductsInCategoryOrderedByPriority(Category $category): array
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
        );

        $paginationResult = $this->productRepository->getPaginationResultForListableInCategory(
            $category,
            Domain::FIRST_DOMAIN_ID,
            $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale(),
            new ProductFilterData(),
            ProductListOrderingConfig::ORDER_BY_PRIORITY,
            $pricingGroup,
            1,
            PHP_INT_MAX,
        );

        return $paginationResult->getResults();
    }
}
