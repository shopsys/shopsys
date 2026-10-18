<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductFacade;
use Elasticsearch\Client;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Uuid;
use Redis;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Shopsys\FrameworkBundle\Model\Product\ProductDomain;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFactory;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class PromotedProductsCacheInvalidationTest extends GraphQlTestCase
{
    private const int PRODUCT_REFERENCE_ID = 33;
    private const string CACHE_KEY = 'queryCache:PromotedProductsQuery:anonymous';
    private const string OTHER_QUERY_CACHE_KEY = 'queryCache:NavigationQuery:anonymous';

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
    private ProductRecalculationDispatcher $productRecalculationDispatcher;

    /**
     * @inject
     */
    private ProductRecalculationFacade $productRecalculationFacade;

    /**
     * @inject
     */
    private TopProductFactory $topProductFactory;

    /**
     * @inject
     */
    private Client $elasticsearchClient;

    /**
     * @inject
     */
    private IndexDefinitionLoader $indexDefinitionLoader;

    private string $originalRedisPrefix;

    /**
     * @var array<array{_index: string, _id: string, found: bool, _source?: array<string, mixed>}>
     */
    private array $otherDomainProductDocuments = [];

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->originalRedisPrefix = (string)$this->storefrontGraphqlQueryClient->getOption(Redis::OPT_PREFIX);
        $this->storefrontGraphqlQueryClient->setOption(Redis::OPT_PREFIX, $this->originalRedisPrefix . 'ssp4502:' . Uuid::uuid4()->toString() . ':');
    }

    #[Override]
    protected function tearDown(): void
    {
        // The shared test helper restores only the first domain's index snapshot.
        foreach ($this->otherDomainProductDocuments as $document) {
            $params = ['index' => $document['_index'], 'id' => $document['_id']];

            if ($document['found']) {
                $this->elasticsearchClient->index($params + ['body' => $document['_source'], 'refresh' => true]);
            } elseif ($this->elasticsearchClient->exists($params)) {
                $this->elasticsearchClient->delete($params + ['refresh' => true]);
            }
        }

        $this->storefrontGraphqlQueryClient->del(self::CACHE_KEY, self::OTHER_QUERY_CACHE_KEY, CleanStorefrontCacheFacade::PROMOTED_PRODUCTS_CACHE_VERSION_KEY);
        $this->storefrontGraphqlQueryClient->setOption(Redis::OPT_PREFIX, $this->originalRedisPrefix);

        parent::tearDown();
    }

    #[DataProvider('getProductChanges')]
    public function testUnavailableProductDisappearsFromPromotedProductsWithoutWaitingForCache(string $change): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::PRODUCT_REFERENCE_ID, Product::class);
        $productId = $product->getId();
        $this->backupProductOnOtherDomains($productId);
        $topProduct = $this->topProductFactory->create($product, $this->domain->getId(), 0);
        $this->em->persist($topProduct);
        $this->em->flush();
        $this->productRecalculationDispatcher->dispatchSingleProductId($productId);
        $this->handleDispatchedRecalculationMessages([$productId]);
        $indexDefinition = $this->indexDefinitionLoader->getIndexDefinition(ExtendedClassNameResolver::resolve(ProductIndex::class)::getName(), $this->domain->getId());
        $this->elasticsearchClient->indices()->refresh(['index' => $indexDefinition->getIndexAlias()]);
        $this->assertContains($productId, $this->getPromotedProductIds());
        $this->storefrontGraphqlQueryClient->setex(self::CACHE_KEY, 3600, 'cached promoted products');
        $this->storefrontGraphqlQueryClient->setex(self::OTHER_QUERY_CACHE_KEY, 3600, 'cached navigation');
        $this->storefrontGraphqlQueryClient->set(CleanStorefrontCacheFacade::PROMOTED_PRODUCTS_CACHE_VERSION_KEY, '7');

        if ($change === 'delete') {
            $this->productFacade->delete($productId);
        } else {
            $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::PRODUCT_REFERENCE_ID, Product::class);
            $productDomain = $this->em->getRepository(ProductDomain::class)->findOneBy([
                'product' => $product,
                'domainId' => $this->domain->getId(),
            ]);
            $this->assertNotNull($productDomain);

            if ($change === 'hide') {
                $productDomain->setDomainHidden(true);
            } else {
                $productDomain->setSellingDenied(true);
            }

            $this->em->flush();
            $this->productRecalculationDispatcher->dispatchSingleProductId($productId);
        }

        $this->handleDispatchedRecalculationMessages([$productId]);

        $this->assertFalse($this->storefrontGraphqlQueryClient->get(self::CACHE_KEY));
        $this->assertSame('cached navigation', $this->storefrontGraphqlQueryClient->get(self::OTHER_QUERY_CACHE_KEY));
        $this->assertSame('8', $this->storefrontGraphqlQueryClient->get(CleanStorefrontCacheFacade::PROMOTED_PRODUCTS_CACHE_VERSION_KEY));
        $this->assertNotContains($productId, $this->getPromotedProductIds());

        if ($change !== 'delete') {
            return;
        }

        $this->storefrontGraphqlQueryClient->setex(self::CACHE_KEY, 3600, 'cached promoted products');

        $this->productRecalculationFacade->recalculate([$productId], ProductRecalculationPriorityEnum::REGULAR);

        $this->assertFalse($this->storefrontGraphqlQueryClient->get(self::CACHE_KEY));
        $this->assertSame('9', $this->storefrontGraphqlQueryClient->get(CleanStorefrontCacheFacade::PROMOTED_PRODUCTS_CACHE_VERSION_KEY));
    }

    public function testRecalculationOfNonPromotedProductPreservesCache(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::PRODUCT_REFERENCE_ID, Product::class);
        $productId = $product->getId();
        $this->backupProductOnOtherDomains($productId);
        $this->storefrontGraphqlQueryClient->setex(self::CACHE_KEY, 3600, 'cached promoted products');
        $this->storefrontGraphqlQueryClient->set(CleanStorefrontCacheFacade::PROMOTED_PRODUCTS_CACHE_VERSION_KEY, '7');

        $this->productRecalculationDispatcher->dispatchSingleProductId($productId);
        $this->handleDispatchedRecalculationMessages([$productId]);

        $this->assertSame('cached promoted products', $this->storefrontGraphqlQueryClient->get(self::CACHE_KEY));
        $this->assertSame('7', $this->storefrontGraphqlQueryClient->get(CleanStorefrontCacheFacade::PROMOTED_PRODUCTS_CACHE_VERSION_KEY));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function getProductChanges(): iterable
    {
        yield 'deleted product' => ['delete'];

        yield 'hidden product' => ['hide'];

        yield 'selling denied product' => ['sellingDenied'];
    }

    /**
     * @return int[]
     */
    private function getPromotedProductIds(): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/PromotedProductIdsQuery.graphql');
        $data = $this->getResponseDataForGraphQlType($response, 'promotedProducts');

        return array_column($data, 'id');
    }

    private function backupProductOnOtherDomains(int $productId): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            if ($domainId === Domain::FIRST_DOMAIN_ID) {
                continue;
            }

            $indexDefinition = $this->indexDefinitionLoader->getIndexDefinition(ExtendedClassNameResolver::resolve(ProductIndex::class)::getName(), $domainId);
            $result = $this->elasticsearchClient->mget([
                'index' => $indexDefinition->getIndexAlias(),
                'body' => ['ids' => [$productId]],
            ]);
            $this->otherDomainProductDocuments[] = $result['docs'][0];
        }
    }
}
