<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\MultipleSearchItemFailedException;

class MultipleSearchFacade
{
    public function __construct(
        protected readonly Client $client,
    ) {
    }

    /**
     * @param array<int|string, array{index: string, body: array<string, mixed>}> $searchQueriesIndexedByKey
     * @return array<int|string, array<string, mixed>> responses indexed by the same keys as the search queries
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
                throw new MultipleSearchItemFailedException($key, $response['error']);
            }

            $responsesIndexedByKey[$key] = $response;
        }

        return $responsesIndexedByKey;
    }
}
