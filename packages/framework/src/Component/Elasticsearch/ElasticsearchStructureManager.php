<?php

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchMoreThanOneCurrentIndexException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchNoCurrentIndexException;

class ElasticsearchStructureManager
{
    /**
     * @var string
     */
    protected $buildVersion;

    /**
     * @var string
     */
    protected $definitionDirectory;

    /**
     * @var string
     */
    protected $indexPrefix;

    /**
     * @var \Elasticsearch\Client
     */
    protected $client;

    /**
     * @param string $buildVersion
     * @param string $definitionDirectory
     * @param string $indexPrefix
     * @param \Elasticsearch\Client $client
     */
    public function __construct(string $buildVersion, string $definitionDirectory, string $indexPrefix, Client $client)
    {
        $this->buildVersion = $buildVersion;
        $this->definitionDirectory = $definitionDirectory;
        $this->indexPrefix = $indexPrefix;
        $this->client = $client;
    }

    /**
     * @param int $domainId
     * @param string $index
     * @return string
     */
    public function getCurrentIndexName(int $domainId, string $index): string
    {
        $aliasName = $this->getAliasName($domainId, $index);

        $indexNames = $this->getExistingIndexNamesForAlias($aliasName);

        if (count($indexNames) > 1) {
            throw new ElasticsearchMoreThanOneCurrentIndexException($aliasName);
        } elseif (count($indexNames) === 0) {
            throw new ElasticsearchNoCurrentIndexException($aliasName);
        }

        return reset($indexNames);
    }

    /**
     * @param string $aliasName
     * @return array
     */
    protected function getExistingIndexNamesForAlias(string $aliasName): array
    {
        $existingIndexNames = [];
        $indexes = $this->client->indices();

        if ($indexes->existsAlias(['name' => $aliasName])) {
            $aliases = $indexes->getAlias([
                'name' => $aliasName,
            ]);
            foreach (array_keys($aliases) as $indexName) {
                if ($indexes->exists(['index' => $indexName])) {
                    $existingIndexNames[] = $indexName;
                }
            }
        }

        return $existingIndexNames;
    }

    /**
     * @param int $domainId
     * @param string $index
     * @return string
     */
    public function getAliasName(int $domainId, string $index): string
    {
        return $this->indexPrefix . $index . '_' . $domainId;
    }
}
