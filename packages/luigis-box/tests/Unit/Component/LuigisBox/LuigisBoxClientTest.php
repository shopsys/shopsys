<?php

declare(strict_types=1);

namespace Tests\LuigisBoxBundle\Unit\Component\LuigisBox;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxSearchBatchLoadData;
use Shopsys\LuigisBoxBundle\Model\Endpoint\LuigisBoxEndpointEnum;
use Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpClient\TraceableHttpClient;

class LuigisBoxClientTest extends TestCase
{
    private const string USER_IDENTIFIER = '123e4567-e89b-12d3-a456-426614174000';
    private const string TRACKER_ID = 'tracker-id';

    public function testSearchRequestUsesOnlyMainTypeAndIgnoresQuicksearchHits(): void
    {
        $requestedUrls = [];
        $client = $this->createClient(
            [
                [
                    'results' => [
                        'hits' => [
                            ['url' => 'category-7'],
                        ],
                        'quicksearch_hits' => [
                            ['url' => 'category-13'],
                        ],
                        'total_hits' => 1,
                        'facets' => [],
                    ],
                ],
            ],
            $requestedUrls,
        );
        $batchLoadData = new LuigisBoxSearchBatchLoadData(
            TypeInLuigisBoxEnum::CATEGORY,
            LuigisBoxEndpointEnum::SEARCH,
            self::USER_IDENTIFIER,
            20,
            'hermiona',
            0,
            ['f' => ['type:category']],
        );

        $results = $client->getData($batchLoadData, [TypeInLuigisBoxEnum::CATEGORY => 20]);

        self::assertCount(1, $requestedUrls);
        self::assertStringContainsString('/search/?tracker_id=' . self::TRACKER_ID, $requestedUrls[0]);
        self::assertStringContainsString('&q=hermiona', $requestedUrls[0]);
        self::assertStringContainsString('&size=20', $requestedUrls[0]);
        self::assertStringContainsString('&f[]=type%3Acategory', $requestedUrls[0]);
        self::assertStringNotContainsString('quicksearch_types', $requestedUrls[0]);
        self::assertSame([7], $results[TypeInLuigisBoxEnum::CATEGORY]->getIds());
        self::assertArrayNotHasKey(TypeInLuigisBoxEnum::ARTICLE, $results);
    }

    public function testAutocompleteRequestKeepsMultipleTypesInSingleRequestAndCountsResultsByType(): void
    {
        $requestedUrls = [];
        $client = $this->createClient(
            [
                [
                    'hits' => [
                        ['url' => 'product-11'],
                        ['url' => 'product-12'],
                        ['url' => 'article-15'],
                        ['url' => 'category-7'],
                        ['url' => 'category-13'],
                        ['url' => 'brand-41'],
                    ],
                    'exact_match_hits_count' => 6,
                    'partial_match_hits_count' => 0,
                ],
            ],
            $requestedUrls,
        );
        $batchLoadData = new LuigisBoxSearchBatchLoadData(
            TypeInLuigisBoxEnum::PRODUCT,
            LuigisBoxEndpointEnum::AUTOCOMPLETE,
            self::USER_IDENTIFIER,
            5,
            'shipping',
            0,
        );

        $results = $client->getData($batchLoadData, [
            TypeInLuigisBoxEnum::PRODUCT => 5,
            TypeInLuigisBoxEnum::ARTICLE => 50,
            TypeInLuigisBoxEnum::CATEGORY => 10,
            TypeInLuigisBoxEnum::BRAND => 50,
        ]);

        self::assertCount(1, $requestedUrls);
        self::assertStringContainsString('/autocomplete/v2/?tracker_id=' . self::TRACKER_ID, $requestedUrls[0]);
        self::assertStringContainsString('&q=shipping', $requestedUrls[0]);
        self::assertStringContainsString('&type=item:5,article:50,category:10,brand:50', $requestedUrls[0]);
        self::assertSame([11, 12], $results[TypeInLuigisBoxEnum::PRODUCT]->getIds());
        self::assertSame(2, $results[TypeInLuigisBoxEnum::PRODUCT]->getItemsCount());
        self::assertSame([15], $results[TypeInLuigisBoxEnum::ARTICLE]->getIds());
        self::assertSame(1, $results[TypeInLuigisBoxEnum::ARTICLE]->getItemsCount());
        self::assertSame([7, 13], $results[TypeInLuigisBoxEnum::CATEGORY]->getIds());
        self::assertSame(2, $results[TypeInLuigisBoxEnum::CATEGORY]->getItemsCount());
        self::assertSame([41], $results[TypeInLuigisBoxEnum::BRAND]->getIds());
        self::assertSame(1, $results[TypeInLuigisBoxEnum::BRAND]->getItemsCount());
    }

    public function testMultipleDataRequestsAreSentBeforeResponsesAreReadAndFailureIsIsolated(): void
    {
        /** @var string[] $events */
        $events = [];
        /** @var string[] $requestedUrls */
        $requestedUrls = [];
        $requestsCount = 0;
        /** @var array<int, array{body: array<string, mixed>, httpCode: int}> $responseQueue */
        $responseQueue = [
            [
                'body' => [
                    'results' => [
                        'hits' => [
                            ['url' => 'product-11'],
                        ],
                        'total_hits' => 1,
                        'facets' => [],
                    ],
                ],
                'httpCode' => 200,
            ],
            [
                'body' => [
                    'error' => 'Luigi\'s Box category search failed.',
                ],
                'httpCode' => 500,
            ],
            [
                'body' => [
                    'results' => [
                        'hits' => [
                            ['url' => 'article-31'],
                        ],
                        'total_hits' => 1,
                        'facets' => [],
                    ],
                ],
                'httpCode' => 200,
            ],
        ];
        $domain = $this->createStub(Domain::class);
        $domain->method('getId')->willReturn(1);
        $logger = $this->createMock(Logger::class);
        $logger->expects($this->once())->method('error');
        $httpClient = new MockHttpClient(
            static function (
                string $methodName,
                string $url,
                array $requestOptions = [],
            ) use (&$events, &$requestedUrls, &$requestsCount, &$responseQueue): MockResponse {
                $requestsCount++;
                $requestedUrls[] = $url;
                $events[] = 'request ' . $requestsCount;
                $responseDefinition = array_shift($responseQueue);

                return new MockResponse(
                    self::createTrackedJsonBody($responseDefinition['body'], $requestsCount, $events, $requestsCount),
                    ['http_code' => $responseDefinition['httpCode']],
                );
            },
        );
        $client = new LuigisBoxClient(
            'https://live.luigisbox.tech/',
            [1 => self::TRACKER_ID],
            $domain,
            $logger,
            new LuigisBoxEndpointEnum(),
            new TraceableHttpClient($httpClient),
        );

        $results = $client->getDataForMultiple([
            $this->createSearchBatchLoadData(TypeInLuigisBoxEnum::PRODUCT, 5),
            $this->createSearchBatchLoadData(TypeInLuigisBoxEnum::CATEGORY, 10),
            $this->createSearchBatchLoadData(TypeInLuigisBoxEnum::ARTICLE, 50),
        ]);

        self::assertCount(3, $requestedUrls);
        self::assertSame([
            'request 1',
            'request 2',
            'request 3',
            'read 1 after 3 requests',
            'read 2 after 3 requests',
            'read 3 after 3 requests',
        ], $events);
        self::assertSame([11], $results[0][TypeInLuigisBoxEnum::PRODUCT]->getIds());
        self::assertSame([], $results[1][TypeInLuigisBoxEnum::CATEGORY]->getIds());
        self::assertSame([31], $results[2][TypeInLuigisBoxEnum::ARTICLE]->getIds());
    }

    public function testMultipleDataRequestsContinueWhenRequestCreationFails(): void
    {
        /** @var string[] $requestedUrls */
        $requestedUrls = [];
        $requestsCount = 0;
        $domain = $this->createStub(Domain::class);
        $domain->method('getId')->willReturn(1);
        $logger = $this->createMock(Logger::class);
        $logger->expects($this->once())->method('error');
        $httpClient = new MockHttpClient(
            static function (
                string $methodName,
                string $url,
                array $requestOptions = [],
            ) use (&$requestedUrls, &$requestsCount): MockResponse {
                $requestsCount++;
                $requestedUrls[] = $url;

                if ($requestsCount === 2) {
                    throw new TransportException('Luigi\'s Box transport failed.');
                }

                return new MockResponse(json_encode([
                    'results' => [
                        'hits' => [
                            ['url' => $requestsCount === 1 ? 'product-11' : 'article-31'],
                        ],
                        'total_hits' => 1,
                        'facets' => [],
                    ],
                ], JSON_THROW_ON_ERROR));
            },
        );
        $client = new LuigisBoxClient(
            'https://live.luigisbox.tech/',
            [1 => self::TRACKER_ID],
            $domain,
            $logger,
            new LuigisBoxEndpointEnum(),
            new TraceableHttpClient($httpClient),
        );

        $results = $client->getDataForMultiple([
            $this->createSearchBatchLoadData(TypeInLuigisBoxEnum::PRODUCT, 5),
            $this->createSearchBatchLoadData(TypeInLuigisBoxEnum::CATEGORY, 10),
            $this->createSearchBatchLoadData(TypeInLuigisBoxEnum::ARTICLE, 50),
        ]);

        self::assertCount(3, $requestedUrls);
        self::assertSame([11], $results[0][TypeInLuigisBoxEnum::PRODUCT]->getIds());
        self::assertSame([], $results[1][TypeInLuigisBoxEnum::CATEGORY]->getIds());
        self::assertSame([31], $results[2][TypeInLuigisBoxEnum::ARTICLE]->getIds());
    }

    public function testMultipleDataRequestsContinueWhenSuccessfulResponsePayloadIsMalformed(): void
    {
        /** @var string[] $requestedUrls */
        $requestedUrls = [];
        $requestsCount = 0;
        /** @var array<int, array<string, mixed>> $responseQueue */
        $responseQueue = [
            [
                'results' => [
                    'hits' => [
                        ['url' => 'product-11'],
                    ],
                    'total_hits' => 1,
                    'facets' => [],
                ],
            ],
            [
                'results' => [
                    'total_hits' => 1,
                    'facets' => [],
                ],
            ],
            [
                'results' => [
                    'hits' => [
                        ['url' => 'article-31'],
                    ],
                    'total_hits' => 1,
                    'facets' => [],
                ],
            ],
        ];
        $domain = $this->createStub(Domain::class);
        $domain->method('getId')->willReturn(1);
        $logger = $this->createMock(Logger::class);
        $logger->expects($this->once())->method('error');
        $httpClient = new MockHttpClient(
            static function (
                string $methodName,
                string $url,
                array $requestOptions = [],
            ) use (&$requestedUrls, &$requestsCount, &$responseQueue): MockResponse {
                $requestsCount++;
                $requestedUrls[] = $url;

                return new MockResponse(json_encode(array_shift($responseQueue), JSON_THROW_ON_ERROR));
            },
        );
        $client = new LuigisBoxClient(
            'https://live.luigisbox.tech/',
            [1 => self::TRACKER_ID],
            $domain,
            $logger,
            new LuigisBoxEndpointEnum(),
            new TraceableHttpClient($httpClient),
        );

        $results = $client->getDataForMultiple([
            $this->createSearchBatchLoadData(TypeInLuigisBoxEnum::PRODUCT, 5),
            $this->createSearchBatchLoadData(TypeInLuigisBoxEnum::CATEGORY, 10),
            $this->createSearchBatchLoadData(TypeInLuigisBoxEnum::ARTICLE, 50),
        ]);

        self::assertCount(3, $requestedUrls);
        self::assertSame([11], $results[0][TypeInLuigisBoxEnum::PRODUCT]->getIds());
        self::assertSame([], $results[1][TypeInLuigisBoxEnum::CATEGORY]->getIds());
        self::assertSame([31], $results[2][TypeInLuigisBoxEnum::ARTICLE]->getIds());
    }

    /**
     * @param array<int, array<string, mixed>> $responses
     * @param string[] $requestedUrls
     */
    private function createClient(array $responses, array &$requestedUrls): LuigisBoxClient
    {
        $domain = $this->createStub(Domain::class);
        $domain->method('getId')->willReturn(1);

        $responseQueue = $responses;
        $httpClient = new MockHttpClient(
            static function (
                string $methodName,
                string $url,
                array $requestOptions = [],
            ) use (&$requestedUrls, &$responseQueue): MockResponse {
                $requestedUrls[] = $url;

                return new MockResponse(json_encode(array_shift($responseQueue), JSON_THROW_ON_ERROR));
            },
        );

        return new LuigisBoxClient(
            'https://live.luigisbox.tech/',
            [1 => self::TRACKER_ID],
            $domain,
            $this->createStub(Logger::class),
            new LuigisBoxEndpointEnum(),
            new TraceableHttpClient($httpClient),
        );
    }

    private function createSearchBatchLoadData(string $type, int $limit): LuigisBoxSearchBatchLoadData
    {
        return new LuigisBoxSearchBatchLoadData(
            $type,
            LuigisBoxEndpointEnum::SEARCH,
            self::USER_IDENTIFIER,
            $limit,
            'shipping',
            0,
            ['f' => ['type:' . $type]],
        );
    }

    /**
     * @param array<string, mixed> $responseData
     * @param string[] $events
     * @return iterable<string>
     */
    private static function createTrackedJsonBody(
        array $responseData,
        int $requestNumber,
        array &$events,
        int &$requestsCount,
    ): iterable {
        $events[] = sprintf('read %d after %d requests', $requestNumber, $requestsCount);

        yield json_encode($responseData, JSON_THROW_ON_ERROR);
    }
}
