<?php

namespace Shopsys\FrameworkBundle\Model\Elasticsearch;

use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Model\Elasticsearch\Exception\ElasticsearchIndexException;

class IndexManager
{
    /**
     * @var \Elasticsearch\Client
     */
    protected $elasticsearchClient;

    /**
     * IndexManager constructor.
     *
     * @param \Elasticsearch\Client $elasticsearchClient
     */
    public function __construct(Client $elasticsearchClient)
    {
        $this->elasticsearchClient = $elasticsearchClient;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Elasticsearch\IndexDefinition $documentDefinition
     */
    public function createIndex(IndexDefinition $documentDefinition): void
    {
        $indexName = $documentDefinition->getVersionedIndexName();
        $indexes = $this->elasticsearchClient->indices();

        if ($indexes->exists(['index' => $indexName])) {
            throw ElasticsearchIndexException::indexAlreadyExists($indexName);
        }

        $result = $indexes->create([
            'index' => $indexName,
            'body' => $documentDefinition->getDefinition(),
        ]);

        if (isset($result['error'])) {
            throw ElasticsearchIndexException::createIndexError($indexName, $result['error']);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Elasticsearch\IndexDefinition $indexDefinition
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
            throw ElasticsearchIndexException::createAliasError($indexName, $result['error']);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Elasticsearch\IndexDefinition $indexDefinition
     */
    public function deleteIndexByIndexDefinition(IndexDefinition $indexDefinition): void
    {
        $indexes = $this->elasticsearchClient->indices();
        foreach ($indexes->getAliases() as $indexName => list('aliases' => $aliases)) {
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
            throw ElasticsearchIndexException::deleteIndexError($indexName, $result['error']);
        }
    }
}
