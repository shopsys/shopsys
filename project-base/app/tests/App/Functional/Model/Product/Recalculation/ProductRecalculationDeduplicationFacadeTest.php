<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Recalculation;

use Nette\Utils\Json;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Redis;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDeduplicationFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum;
use Tests\App\Test\TransactionFunctionalTestCase;

class ProductRecalculationDeduplicationFacadeTest extends TransactionFunctionalTestCase
{
    // This is a value from Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDeduplicationFacade::getCacheKey()
    private const string REDIS_KEY = 'product_recalculation_ids_' . ProductRecalculationPriorityEnum::REGULAR;

    private Redis $redis;

    #[Override]
    protected function setUp(): void
    {
        /** @phpstan-ignore symfonyContainer.serviceNotFound (service is available only in test env) */
        $this->redis = self::getContainer()->get('snc_redis.test');

        parent::setUp();
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->redis->del(self::REDIS_KEY);

        parent::tearDown();
    }

    /**
     * @param array<int, string[]> $initialData
     * @param int[] $productIds
     * @param array<int, string[]> $expectedOutput
     */
    #[DataProvider('getScopesIndexedByProductIdProvider')]
    public function testGetScopesIndexedByProductId(array $initialData, array $productIds, array $expectedOutput): void
    {
        $productRecalculationDeduplicationFacade = new ProductRecalculationDeduplicationFacade($this->redis, true);

        $this->preSetRedisData($initialData);

        $result = $productRecalculationDeduplicationFacade->getScopesIndexedByProductId($productIds, ProductRecalculationPriorityEnum::REGULAR);

        $this->assertSame($expectedOutput, $result);
    }

    /**
     * @return array
     */
    public static function getScopesIndexedByProductIdProvider(): array
    {
        return [
            'empty redis' => [
                'initialData' => [],
                'productIds' => [1, 2],
                'expectedOutput' => [
                    1 => [],
                    2 => [],
                ],
            ],
            'requested already stored id' => [
                'initialData' => [
                    1 => ['scope1'],
                    2 => ['scope2'],
                ],
                'productIds' => [1, 2],
                'expectedOutput' => [
                    1 => ['scope1'],
                    2 => ['scope2'],
                ],
            ],
            'requested not stored id' => [
                'initialData' => [
                    1 => ['scope1'],
                    2 => ['scope2'],
                ],
                'productIds' => [3],
                'expectedOutput' => [
                    3 => [],
                ],
            ],
            'requested mixed' => [
                'initialData' => [
                    1 => ['scope1'],
                    2 => ['scope2'],
                ],
                'productIds' => [1, 3],
                'expectedOutput' => [
                    1 => ['scope1'],
                    3 => [],
                ],
            ],
        ];
    }

    /**
     * @param array<int, string[]> $initialData
     * @param int[] $productIds
     * @param string[] $scopes
     * @param int[] $expectedOutput
     * @param array<int, string[]> $expectedStoredData
     */
    #[DataProvider('updateScopesAndReturnProductIdsToDispatchProvider')]
    public function testUpdateScopesAndReturnProductIdsToDispatch(
        array $initialData,
        array $productIds,
        array $scopes,
        array $expectedOutput,
        array $expectedStoredData,
    ): void {
        $productRecalculationDeduplicationFacade = new ProductRecalculationDeduplicationFacade($this->redis, true);
        $this->preSetRedisData($initialData);

        $result = $productRecalculationDeduplicationFacade->updateScopesAndReturnProductIdsToDispatch(
            $productIds,
            $scopes,
            ProductRecalculationPriorityEnum::REGULAR,
        );

        $this->assertSame($expectedOutput, $result, 'Product IDs to dispatch are not as expected');

        $storedData = $this->fetchRedisData();
        $this->assertEquals($expectedStoredData, $storedData, 'Stored data is not as expected');
    }

    /**
     * @return array
     */
    public static function updateScopesAndReturnProductIdsToDispatchProvider(): array
    {
        return [
            'empty redis; all passed ids are returned; passed export scopes stored' => [
                'initialData' => [],
                'productIds' => [1, 2],
                'scopes' => ['scope1', 'scope2'],
                'expectedOutput' => [1, 2],
                'expectedStoredData' => [1 => ['scope1', 'scope2'], 2 => ['scope1', 'scope2']],
            ],
            'passed product id is not in initial data; passed scope is stored, product id returned' => [
                'initialData' => [1 => ['scope1']],
                'productIds' => [1, 2],
                'scopes' => ['scope1'],
                'expectedOutput' => [2],
                'expectedStoredData' => [1 => ['scope1'], 2 => ['scope1']],
            ],
            'already stored product id with scope [] (all); no id returned, no change in stored data' => [
                'initialData' => [1 => []],
                'productIds' => [1],
                'scopes' => [],
                'expectedOutput' => [],
                'expectedStoredData' => [1 => []],
            ],
            'initial data has some scope; passing scope []; scope [] is stored; product id is not returned' => [
                'initialData' => [1 => ['scope1']],
                'productIds' => [1],
                'scopes' => [],
                'expectedOutput' => [],
                'expectedStoredData' => [1 => []],
            ],
            'passing new scope; scope is added to stored data; product id is not returned' => [
                'initialData' => [1 => ['scope1']],
                'productIds' => [1],
                'scopes' => ['scope2'],
                'expectedOutput' => [],
                'expectedStoredData' => [1 => ['scope1', 'scope2']],
            ],
        ];
    }

    /**
     * @param array<int, string[]> $initialData
     */
    public function preSetRedisData(array $initialData): void
    {
        $this->redis->del(self::REDIS_KEY);

        foreach ($initialData as $productId => $scopes) {
            $this->redis->hSet(self::REDIS_KEY, (string)$productId, Json::encode($scopes));
        }
    }

    /**
     * @return array<int, string[]>
     */
    private function fetchRedisData(): array
    {
        $storedData = $this->redis->hGetAll(self::REDIS_KEY);

        return array_map(
            static fn ($value) => Json::decode($value, true),
            $storedData,
        );
    }
}
