<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Component\Elasticsearch;

use Overblog\DataLoader\DataLoader;
use Overblog\PromiseAdapter\Adapter\WebonyxGraphQLSyncPromiseAdapter;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Elasticsearch\MultipleSearchRepository;
use Shopsys\FrontendApiBundle\Component\Elasticsearch\ElasticsearchSearchBatchLoader;

class ElasticsearchSearchBatchLoaderTest extends TestCase
{
    public function testQueriesOfSeveralListingsAreSentInOneMultipleSearch(): void
    {
        $firstListingQuery = [
            'index' => 'product_1',
            'body' => [
                'query' => [
                    'term' => [
                        'brand' => 1,
                    ],
                ],
            ],
        ];
        $secondListingQuery = [
            'index' => 'product_1',
            'body' => [
                'query' => [
                    'term' => [
                        'brand' => 2,
                    ],
                ],
            ],
        ];
        $firstListingResponse = [
            'hits' => [
                'total' => [
                    'value' => 1,
                ],
            ],
        ];
        $secondListingResponse = [
            'hits' => [
                'total' => [
                    'value' => 2,
                ],
            ],
        ];

        $multipleSearchRepositoryMock = $this->createMock(MultipleSearchRepository::class);
        $multipleSearchRepositoryMock->expects($this->once())
            ->method('searchIndexedByKey')
            ->with([$firstListingQuery, $secondListingQuery])
            ->willReturn([$firstListingResponse, $secondListingResponse]);

        $dataLoaderPromiseAdapter = new WebonyxGraphQLSyncPromiseAdapter();
        $elasticsearchSearchBatchLoader = new ElasticsearchSearchBatchLoader(
            $dataLoaderPromiseAdapter->getWebonyxPromiseAdapter(),
            $multipleSearchRepositoryMock,
        );
        $dataLoader = new DataLoader($elasticsearchSearchBatchLoader->loadByQueries(...), $dataLoaderPromiseAdapter);

        $firstListingPromise = $dataLoader->load($firstListingQuery);
        $bothListingsPromise = $dataLoader->loadMany([
            'second' => $secondListingQuery,
            'first' => $firstListingQuery,
        ]);

        $this->assertSame($firstListingResponse, DataLoader::await($firstListingPromise));
        $this->assertSame(
            [
                'second' => $secondListingResponse,
                'first' => $firstListingResponse,
            ],
            DataLoader::await($bothListingsPromise),
        );
    }
}
