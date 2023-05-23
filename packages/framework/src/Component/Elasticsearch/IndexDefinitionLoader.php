<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

class IndexDefinitionLoader
{
    /**
     * @param string $indexDefinitionsDirectory
     * @param string $indexPrefix
     */
    public function __construct(
        protected readonly string $indexDefinitionsDirectory,
        protected readonly string $indexPrefix,
    ) {
    }

    /**
     * @param string $indexName
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition
     */
    public function getIndexDefinition(string $indexName, int $domainId): IndexDefinition
    {
        return new IndexDefinition($indexName, $this->indexDefinitionsDirectory, $this->indexPrefix, $domainId);
    }
}
