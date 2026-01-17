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
        $result = $this->client->search($filterQuery->getQuery());

        return array_map(function ($value) {
            $data = $value['_source'];
            $data['id'] = (int)$value['_id'];

            return $this->fillEmptyFields($data);
        }, $result['hits']['hits']);
    }

    public function getTotalCount(AbstractFilterQuery $filterQuery): int
    {
        $result = $this->client->search($filterQuery->getQuery());

        return (int)$result['hits']['total']['value'];
    }
}
