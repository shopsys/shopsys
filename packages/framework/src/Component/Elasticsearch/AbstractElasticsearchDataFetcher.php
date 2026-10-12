<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchNoResultException;

abstract class AbstractElasticsearchDataFetcher
{
    public function __construct(protected Client $client)
    {
    }

    abstract protected function fillEmptyFields(array $data): array;

    public function getSingleResult(AbstractFilterQuery $filterQuery): array
    {
        $singleItemQuery = $filterQuery->setLimit(1);
        $results = $this->getAllResults($singleItemQuery);

        if (count($results) === 0) {
            throw new ElasticsearchNoResultException();
        }

        return array_shift($results);
    }

    public function getAllResults(AbstractFilterQuery $filterQuery): array
    {
        return $this->extractHits($this->client->search($filterQuery->getQuery()));
    }

    public function getTotalCount(AbstractFilterQuery $filterQuery): int
    {
        return $this->extractTotalCount($this->client->search($filterQuery->getQuery()));
    }

    /**
     * @param array<string, mixed> $result
     * @return array<int, array<string, mixed>>
     */
    public function extractHits(array $result): array
    {
        return array_map(function ($value) {
            $data = $value['_source'];
            $data['id'] = (int)$value['_id'];

            return $this->fillEmptyFields($data);
        }, $result['hits']['hits']);
    }

    /**
     * @param array<string, mixed> $result
     */
    public function extractTotalCount(array $result): int
    {
        return (int)$result['hits']['total']['value'];
    }
}
