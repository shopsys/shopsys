<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchNoResultException;

abstract class AbstractElasticsearchDataFetcher
{
    /**
     * @param \Elasticsearch\Client $client
     */
    public function __construct(protected Client $client)
    {
    }

    /**
     * @param array $data
     * @return array
     */
    abstract protected function fillEmptyFields(array $data): array;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractFilterQuery $filterQuery
     * @return array
     */
    public function getSingleResult(AbstractFilterQuery $filterQuery): array
    {
        $results = $this->getAllResults($filterQuery);

        if (count($results) === 0) {
            throw new ElasticsearchNoResultException();
        }

        return array_shift($results);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractFilterQuery $filterQuery
     * @return array
     */
    public function getAllResults(AbstractFilterQuery $filterQuery): array
    {
        $result = $this->client->search($filterQuery->getQuery());

        return array_map(function ($value) {
            $data = $value['_source'];
            $data['id'] = (int)$value['_id'];

            return $this->fillEmptyFields($data);
        }, $result['hits']['hits']);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractFilterQuery $filterQuery
     * @return int
     */
    public function getTotalCount(AbstractFilterQuery $filterQuery): int
    {
        $result = $this->client->search($filterQuery->getQuery());

        return (int)$result['hits']['total']['value'];
    }
}
