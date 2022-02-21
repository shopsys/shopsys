<?php

namespace Tests\ShopBundle\Functional\Model\Product;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\ShopBundle\DataFixtures\Demo\CategoryDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\PricingGroupDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\ProductDataFixture;
use Shopsys\ShopBundle\Model\Category\Category;
use Shopsys\ShopBundle\Model\Product\Product;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class ProductRepositoryTest extends TransactionFunctionalTestCase
{
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
        $this->getAllListableQueryBuilderTest(148, true);
    }

    /**
     * @param mixed $productReferenceId
     * @param mixed $isExpectedInResult
     */
    private function getAllListableQueryBuilderTest($productReferenceId, $isExpectedInResult)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository */
        $productRepository = $this->getContainer()->get(ProductRepository::class);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReference(PricingGroupDataFixture::PRICING_GROUP_ORDINARY_DOMAIN_1);

        /** @var \Shopsys\FrameworkBundle\Component\Domain\Domain $domain */
        $domain = $this->getContainer()->get(Domain::class);

        /** @var \Shopsys\ShopBundle\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productReferenceId);
        $productId = $product->getId();

        $queryBuilder = $productRepository->getAllListableQueryBuilder($domain->getId(), $pricingGroup);
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
        $this->getAllSellableQueryBuilderTest(148, false);
    }

    /**
     * @param mixed $productReferenceId
     * @param mixed $isExpectedInResult
     */
    private function getAllSellableQueryBuilderTest($productReferenceId, $isExpectedInResult)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository */
        $productRepository = $this->getContainer()->get(ProductRepository::class);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReference(PricingGroupDataFixture::PRICING_GROUP_ORDINARY_DOMAIN_1);

        /** @var \Shopsys\FrameworkBundle\Component\Domain\Domain $domain */
        $domain = $this->getContainer()->get(Domain::class);

        /** @var \Shopsys\ShopBundle\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productReferenceId);
        $productId = $product->getId();

        $queryBuilder = $productRepository->getAllSellableQueryBuilder($domain->getId(), $pricingGroup);
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
        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository */
        $productRepository = $this->getContainer()->get(ProductRepository::class);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReference(PricingGroupDataFixture::PRICING_GROUP_ORDINARY_DOMAIN_1);

        /** @var \Shopsys\FrameworkBundle\Component\Domain\Domain $domain */
        $domain = $this->getContainer()->get(Domain::class);
        /** @var \Shopsys\ShopBundle\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productReferenceId);
        $productId = $product->getId();

        $queryBuilder = $productRepository->getAllOfferedQueryBuilder($domain->getId(), $pricingGroup);
        $queryBuilder->andWhere('p.id = :id')
            ->setParameter('id', $productId);
        $result = $queryBuilder->getQuery()->execute();

        $this->assertSame(in_array($product, $result, true), $isExpectedInResult);
    }

    public function testOrderingByProductPriorityInCategory()
    {
        /** @var \Shopsys\ShopBundle\DataFixtures\Demo\CategoryDataFixture $category */
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

    public function testOrderingByProductPriorityInSearch()
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

    public function testGetSortedProductsByIds()
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository */
        $productRepository = $this->getContainer()->get(ProductRepository::class);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReference(PricingGroupDataFixture::PRICING_GROUP_ORDINARY_DOMAIN_1);

        /** @var \Shopsys\FrameworkBundle\Component\Domain\Domain $domain */
        $domain = $this->getContainer()->get(Domain::class);
        /** @var \Shopsys\ShopBundle\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        /** @var \Shopsys\ShopBundle\Model\Product\Product $product2 */
        $product2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 2);
        /** @var \Shopsys\ShopBundle\Model\Product\Product $product3 */
        $product3 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 3);
        /** @var \Shopsys\ShopBundle\Model\Product\Product $product4 */
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

        $results = $productRepository->getOfferedByIds($domain->getId(), $pricingGroup, $sortedProductIds);

        $this->assertSame($sortedProducts, $results);
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Product\Product $product
     * @param int $priority
     */
    private function setProductOrderingPriority(Product $product, $priority)
    {
        /** @var \Shopsys\ShopBundle\Model\Product\ProductDataFactory $productDataFactory */
        $productDataFactory = $this->getContainer()->get(ProductDataFactoryInterface::class);
        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade */
        $productFacade = $this->getContainer()->get(ProductFacade::class);

        $productData = $productDataFactory->createFromProduct($product);
        $productData->orderingPriority = $priority;
        $productFacade->edit($product->getId(), $productData);
    }

    /**
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    private function getProductsForSearchOrderedByPriority($searchText)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository */
        $productRepository = $this->getContainer()->get(ProductRepository::class);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReference(PricingGroupDataFixture::PRICING_GROUP_ORDINARY_DOMAIN_1);
        /** @var \Shopsys\FrameworkBundle\Component\Domain\Domain $domain */
        $domain = $this->getContainer()->get(Domain::class);

        $paginationResult = $productRepository->getPaginationResultForSearchListable(
            $searchText,
            1,
            $domain->getDomainConfigById(1)->getLocale(),
            new ProductFilterData(),
            ProductListOrderingConfig::ORDER_BY_PRIORITY,
            $pricingGroup,
            1,
            PHP_INT_MAX
        );

        return $paginationResult->getResults();
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    private function getProductsInCategoryOrderedByPriority(Category $category)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository */
        $productRepository = $this->getContainer()->get(ProductRepository::class);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReference(PricingGroupDataFixture::PRICING_GROUP_ORDINARY_DOMAIN_1);
        /** @var \Shopsys\FrameworkBundle\Component\Domain\Domain $domain */
        $domain = $this->getContainer()->get(Domain::class);

        $paginationResult = $productRepository->getPaginationResultForListableInCategory(
            $category,
            1,
            $domain->getDomainConfigById(1)->getLocale(),
            new ProductFilterData(),
            ProductListOrderingConfig::ORDER_BY_PRIORITY,
            $pricingGroup,
            1,
            PHP_INT_MAX
        );

        return $paginationResult->getResults();
    }
}
