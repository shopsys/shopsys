<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

class IndexDefinitionLoader
{
    /**
     * @param string $indexDefinitionsDirectory
     * @param string $indexPrefix
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionModifier $indexDefinitionModifier
     */
    public function __construct(
        protected readonly string $indexDefinitionsDirectory,
        protected readonly string $indexPrefix,
        protected readonly IndexDefinitionModifier $indexDefinitionModifier,
    ) {
    }

    /**
     * @param string $indexName
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition
     */
    public function getIndexDefinition(string $indexName, int $domainId): IndexDefinition
    {
        return new IndexDefinition($indexName, $this->indexDefinitionsDirectory, $this->indexPrefix, $domainId, $this->indexDefinitionModifier);
    }
}
