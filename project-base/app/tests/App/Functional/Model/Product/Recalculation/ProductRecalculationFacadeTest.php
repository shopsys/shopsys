<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Recalculation;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use Override;
use Redis;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

class ProductRecalculationFacadeTest extends TransactionFunctionalTestCase
{
    private const string PROMOTED_PRODUCTS_CACHE_KEY = 'queryCache:' . CleanStorefrontCacheFacade::PROMOTED_PRODUCTS_QUERY_KEY_PART . ':test';

    /**
     * @inject snc_redis.storefront_graphql_query
     */
    private Redis $storefrontGraphqlQueryClient;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    #[Override]
    protected function tearDown(): void
    {
        $this->storefrontGraphqlQueryClient->del(self::PROMOTED_PRODUCTS_CACHE_KEY);

        parent::tearDown();
    }

    public function testHidingPromotedProductCleansPromotedProductsCache(): void
    {
        $this->storefrontGraphqlQueryClient->set(self::PROMOTED_PRODUCTS_CACHE_KEY, 'cached');

        $this->hideProductOnFirstDomain(1);

        $this->assertFalse($this->storefrontGraphqlQueryClient->get(self::PROMOTED_PRODUCTS_CACHE_KEY));
    }

    public function testHidingNotPromotedProductKeepsPromotedProductsCache(): void
    {
        $this->storefrontGraphqlQueryClient->set(self::PROMOTED_PRODUCTS_CACHE_KEY, 'cached');

        $this->hideProductOnFirstDomain(33);

        $this->assertSame('cached', $this->storefrontGraphqlQueryClient->get(self::PROMOTED_PRODUCTS_CACHE_KEY));
    }

    private function hideProductOnFirstDomain(int $productReferenceId): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productReferenceId, Product::class);
        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->domainHidden[Domain::FIRST_DOMAIN_ID] = true;
        $this->productFacade->edit($product->getId(), $productData);

        $this->handleDispatchedRecalculationMessages();
    }
}
