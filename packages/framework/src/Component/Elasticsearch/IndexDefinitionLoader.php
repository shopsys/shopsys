<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class IndexDefinitionLoader
{
    public function __construct(
        protected readonly string $indexDefinitionsDirectory,
        protected readonly string $indexPrefix,
        protected readonly IndexDefinitionModifier $indexDefinitionModifier,
        protected readonly IndexDefinitionYamlResolver $indexDefinitionYamlResolver,
        protected readonly Domain $domain,
        protected readonly string $environment,
    ) {
    }

    public function getIndexDefinition(string $indexName, int $domainId): IndexDefinition
    {
        $domainConfig = $this->domain->getDomainConfigById($domainId);

        return new IndexDefinition(
            $indexName,
            $this->indexDefinitionsDirectory,
            $this->indexPrefix,
            $domainId,
            $this->indexDefinitionModifier,
            $this->indexDefinitionYamlResolver,
            $domainConfig->getLocale(),
            $this->environment,
        );
    }
}
