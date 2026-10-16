<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchMultipleSearchItemFailedException;

class MultipleSearchRepository
{
    public function __construct(
        protected readonly Client $client,
    ) {
    }

    /**
     * @param array<int|string, array{index: string, body: array<string, mixed>}> $searchQueriesIndexedByKey
     * @return array<int|string, array<string, mixed>>
     */
    public function searchIndexedByKey(array $searchQueriesIndexedByKey): array
    {
        if ($searchQueriesIndexedByKey === []) {
            return [];
        }

        $body = [];

        foreach ($searchQueriesIndexedByKey as $searchQuery) {
            $body[] = ['index' => $searchQuery['index']];
            $body[] = $searchQuery['body'];
        }

        $result = $this->client->msearch(['body' => $body]);
        $keys = array_keys($searchQueriesIndexedByKey);
        $responsesIndexedByKey = [];

        foreach ($result['responses'] as $position => $response) {
            $key = $keys[$position];

            if (isset($response['error'])) {
                throw new ElasticsearchMultipleSearchItemFailedException($key, $response['error']);
            }

            $responsesIndexedByKey[$key] = $response;
        }

        return $responsesIndexedByKey;
    }
}
