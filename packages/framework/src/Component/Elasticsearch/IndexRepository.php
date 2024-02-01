<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchAliasNotFoundException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchBulkUpdateException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchCreateAliasException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchCreateIndexException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchDeleteIndexException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchIndexAliasAlreadyExistsException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchMoreThanOneIndexFoundForAliasException;

class IndexRepository
{
    /**
     * @param \Elasticsearch\Client $elasticsearchClient
     */
    public function __construct(protected readonly Client $elasticsearchClient)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     */
    public function createIndex(IndexDefinition $indexDefinition): void
    {
        $indexName = $indexDefinition->getVersionedIndexName();
        $indexes = $this->elasticsearchClient->indices();

        if ($indexes->exists(['index' => $indexName])) {
            throw new ElasticsearchIndexAliasAlreadyExistsException($indexName);
        }

        $result = $indexes->create([
            'index' => $indexName,
            'body' => $indexDefinition->getDefinition(),
        ]);

        if (isset($result['error'])) {
            throw new ElasticsearchCreateIndexException($indexName, $result['error']);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     */
    public function createAlias(IndexDefinition $indexDefinition): void
    {
        $indexName = $indexDefinition->getVersionedIndexName();
        $indexes = $this->elasticsearchClient->indices();

        $result = $indexes->putAlias([
            'index' => $indexName,
            'name' => $indexDefinition->getIndexAlias(),
        ]);

        if (isset($result['error'])) {
            throw new ElasticsearchCreateAliasException($indexName, $result['error']);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     */
    public function deleteIndexByIndexDefinition(IndexDefinition $indexDefinition): void
    {
        $indexes = $this->elasticsearchClient->indices();

        foreach ($indexes->getAliases() as $indexName => ['aliases' => $aliases]) {
            if (array_key_exists($indexDefinition->getIndexAlias(), $aliases)) {
                $this->deleteIndex($indexName);
            }
        }
    }

    /**
     * @param string $indexName
     */
    public function deleteIndex(string $indexName): void
    {
        $indexes = $this->elasticsearchClient->indices();

        $result = $indexes->delete([
            'index' => $indexName,
        ]);

        if (isset($result['error'])) {
            throw new ElasticsearchDeleteIndexException($indexName, $result['error']);
        }
    }

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
        $params = ['body' => []];

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

    /**
     * @param string $aliasName
     * @return array
     */
    protected function findIndexNamesForAlias(string $aliasName): array
    {
        if (!$this->isAliasCreated($aliasName)) {
            throw new ElasticsearchAliasNotFoundException($aliasName);
        }

        $indexes = $this->elasticsearchClient->indices();

        $indexesWithAlias = array_keys($indexes->getAlias(['name' => $aliasName]));

        if (count($indexesWithAlias) === 0) {
            throw new ElasticsearchAliasNotFoundException($aliasName);
        }

        return $indexesWithAlias;
    }

    /**
     * @param string $aliasName
     * @return bool
     */
    protected function isAliasCreated(string $aliasName): bool
    {
        $indexes = $this->elasticsearchClient->indices();

        return $indexes->existsAlias(['name' => $aliasName]);
    }

    /**
     * @param string $aliasName
     * @return string
     */
    public function findCurrentIndexNameForAlias(string $aliasName): string
    {
        $indexesWithAlias = $this->findIndexNamesForAlias($aliasName);

        if (count($indexesWithAlias) > 1) {
            throw new ElasticsearchMoreThanOneIndexFoundForAliasException($aliasName, $indexesWithAlias);
        }

        return $indexesWithAlias[0];
    }

    /**
     * @param string $sourceIndexName
     * @param string $destinationIndexName
     */
    public function reindex(string $sourceIndexName, string $destinationIndexName): void
    {
        $this->elasticsearchClient->reindex([
            'body' => [
                'source' => [
                    'index' => $sourceIndexName,
                ],
                'dest' => [
                    'index' => $destinationIndexName,
                ],
            ],
        ]);
    }
}
