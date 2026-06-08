<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Elasticsearch;

use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRegistry;
use Throwable;

class ElasticsearchEntityDataFacade
{
    public function __construct(
        protected readonly Client $client,
        protected readonly IndexDefinitionLoader $indexDefinitionLoader,
        protected readonly IndexRegistry $indexRegistry,
        protected readonly ElasticsearchEntityDataFactory $elasticsearchEntityDataFactory,
    ) {
    }

    public function getElasticsearchEntityData(
        string $indexName,
        int $domainId,
        int $entityId,
    ): ElasticsearchEntityData {
        $indexAlias = $this->indexDefinitionLoader->getIndexDefinition($indexName, $domainId)->getIndexAlias();

        $parameters = [
            'index' => $indexAlias,
            'id' => (string)$entityId,
        ];

        try {
            if (!$this->indexRegistry->isIndexRegistered($indexName) || !$this->client->exists($parameters)) {
                return $this->elasticsearchEntityDataFactory->createNotFound($indexAlias, $domainId, $entityId);
            }

            return $this->elasticsearchEntityDataFactory->createFound(
                $indexAlias,
                $domainId,
                $entityId,
                $this->client->get($parameters),
            );
        } catch (Throwable $exception) {
            return $this->elasticsearchEntityDataFactory->createError(
                $indexAlias,
                $domainId,
                $entityId,
                $exception->getMessage(),
            );
        }
    }
}
