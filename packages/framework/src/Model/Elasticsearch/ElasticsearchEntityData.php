<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Elasticsearch;

class ElasticsearchEntityData
{
    public const string STATUS_FOUND = 'found';
    public const string STATUS_NOT_FOUND = 'not_found';
    public const string STATUS_ERROR = 'error';

    /**
     * @param array<string, mixed>|null $rawDocument
     */
    public function __construct(
        protected readonly string $status,
        protected readonly string $indexAlias,
        protected readonly int $domainId,
        protected readonly int $entityId,
        protected readonly ?array $rawDocument = null,
        protected readonly ?string $errorMessage = null,
    ) {
    }

    public function isFound(): bool
    {
        return $this->status === static::STATUS_FOUND;
    }

    public function isNotFound(): bool
    {
        return $this->status === static::STATUS_NOT_FOUND;
    }

    public function isError(): bool
    {
        return $this->status === static::STATUS_ERROR;
    }

    public function getIndexAlias(): string
    {
        return $this->indexAlias;
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRawDocument(): ?array
    {
        return $this->rawDocument;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getPrettyJson(): string
    {
        if ($this->rawDocument === null) {
            return '';
        }

        return json_encode(
            $this->rawDocument,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );
    }
}
