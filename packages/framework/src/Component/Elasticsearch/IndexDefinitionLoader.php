<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

class IndexDefinitionLoader
{
    public function __construct(
        protected readonly string $indexDefinitionsDirectory,
        protected readonly string $indexPrefix,
        protected readonly IndexDefinitionModifier $indexDefinitionModifier,
    ) {
    }

    public function getIndexDefinition(string $indexName, int $domainId): IndexDefinition
    {
        return new IndexDefinition($indexName, $this->indexDefinitionsDirectory, $this->indexPrefix, $domainId, $this->indexDefinitionModifier);
    }
}
