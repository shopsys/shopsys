<?php

declare(strict_types=1);

namespace App\Component\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchBulkUpdateException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository as BaseIndexRepository;

class IndexRepository extends BaseIndexRepository
{
    /**
     * @param string $indexAlias
     * @param int[] $ids
     */
    public function deleteIds(string $indexAlias, array $ids): void
    {
        $this->elasticsearchClient->deleteByQuery([
            'index' => $indexAlias,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            'ids' => [
                                'values' => $ids,
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @param int[] $keepIds
     */
    public function deleteNotPresent(IndexDefinition $indexDefinition, array $keepIds): void
    {
        $this->elasticsearchClient->deleteByQuery([
            'index' => $indexDefinition->getVersionedIndexName(),
            'body' => [
                'query' => [
                    'bool' => [
                        'must_not' => [
                            'ids' => [
                                'values' => $keepIds,
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param string $indexAlias
     * @param array $data
     * @param bool $createIfNotExists
     */
    public function bulkUpdate(string $indexAlias, array $data, bool $createIfNotExists = true): void
    {
        $params = [
            'body' => [],
        ];

        foreach ($data as $id => $row) {
            $params['body'][] = [
                'update' => [
                    '_index' => $indexAlias,
                    '_id' => (string)$id,
                ],
            ];

            $params['body'][] = [
                'doc' => $row,
                'doc_as_upsert' => $createIfNotExists,
            ];
        }

        $result = $this->elasticsearchClient->bulk($params);

        if (isset($result['errors']) && $result['errors'] === true) {
            throw new ElasticsearchBulkUpdateException($indexAlias, $result['items']);
        }
    }
}
