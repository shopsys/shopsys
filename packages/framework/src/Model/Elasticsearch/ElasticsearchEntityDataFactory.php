<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Elasticsearch;

class ElasticsearchEntityDataFactory
{
    /**
     * @param array<string, mixed> $rawDocument
     */
    public function createFound(
        string $indexAlias,
        int $domainId,
        int $entityId,
        array $rawDocument,
    ): ElasticsearchEntityData {
        return new ElasticsearchEntityData(
            ElasticsearchEntityData::STATUS_FOUND,
            $indexAlias,
            $domainId,
            $entityId,
            $rawDocument,
        );
    }

    public function createNotFound(string $indexAlias, int $domainId, int $entityId): ElasticsearchEntityData
    {
        return new ElasticsearchEntityData(
            ElasticsearchEntityData::STATUS_NOT_FOUND,
            $indexAlias,
            $domainId,
            $entityId,
        );
    }

    public function createError(
        string $indexAlias,
        int $domainId,
        int $entityId,
        string $errorMessage,
    ): ElasticsearchEntityData {
        return new ElasticsearchEntityData(
            ElasticsearchEntityData::STATUS_ERROR,
            $indexAlias,
            $domainId,
            $entityId,
            null,
            $errorMessage,
        );
    }
}
