<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Elasticsearch;

use Elasticsearch\Client;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\MultipleSearchItemFailedException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\MultipleSearchFacade;

class MultipleSearchFacadeTest extends TestCase
{
    public function testQueriesAreSentAsOneMultiSearchAndResponsesKeepInputKeys(): void
    {
        $firstQuery = [
            'index' => 'product_1',
            'body' => [
                'size' => 0,
                'query' => [
                    'match_all' => [],
                ],
            ],
        ];
        $secondQuery = ['index' => 'product_2', 'body' => ['size' => 1, 'query' => ['term' => ['brand' => 5]]]];
        $firstResponse = ['hits' => ['total' => ['value' => 3]]];
        $secondResponse = ['hits' => ['total' => ['value' => 1]]];

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('msearch')
            ->with([
                'body' => [
                    ['index' => 'product_1'],
                    $firstQuery['body'],
                    ['index' => 'product_2'],
                    $secondQuery['body'],
                ],
            ])
            ->willReturn(['responses' => [$firstResponse, $secondResponse]]);

        $multipleSearchFacade = new MultipleSearchFacade($client);

        $this->assertSame(
            ['first' => $firstResponse, 'second' => $secondResponse],
            $multipleSearchFacade->searchIndexedByKey(['first' => $firstQuery, 'second' => $secondQuery]),
        );
    }

    public function testEmptyInputSendsNoRequest(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->never())->method('msearch');

        $multipleSearchFacade = new MultipleSearchFacade($client);

        $this->assertSame([], $multipleSearchFacade->searchIndexedByKey([]));
    }

    public function testFailedItemThrowsException(): void
    {
        $client = $this->createStub(Client::class);
        $client->method('msearch')->willReturn([
            'responses' => [
                ['hits' => ['total' => ['value' => 3]]],
                ['error' => ['type' => 'index_not_found_exception', 'reason' => 'no such index [product_2]']],
            ],
        ]);

        $multipleSearchFacade = new MultipleSearchFacade($client);

        $this->expectException(MultipleSearchItemFailedException::class);
        $this->expectExceptionMessage('Elasticsearch multi search item "second" failed');

        $multipleSearchFacade->searchIndexedByKey([
            'first' => ['index' => 'product_1', 'body' => []],
            'second' => ['index' => 'product_2', 'body' => []],
        ]);
    }
}
