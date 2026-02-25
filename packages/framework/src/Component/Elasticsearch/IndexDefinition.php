<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchCannotReadDefinitionFileException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchInvalidJsonInDefinitionFileException;

class IndexDefinition
{
    protected AbstractIndex $index;

    public function __construct(
        protected readonly string $indexName,
        protected readonly string $definitionsDirectory,
        protected readonly string $indexPrefix,
        protected readonly int $domainId,
        protected readonly IndexDefinitionModifier $indexDefinitionModifier,
    ) {
    }

    public function getDefinition(): array
    {
        $decodedDefinition = json_decode($this->getDefinitionFileContent(), true);

        if ($decodedDefinition === null) {
            throw new ElasticsearchInvalidJsonInDefinitionFileException(
                $this->getIndexName(),
                $this->getDefinitionFilepath(),
            );
        }

        return $this->indexDefinitionModifier->modifyDefinition($decodedDefinition);
    }

    protected function getDefinitionFilepath(): string
    {
        return $this->definitionsDirectory . $this->getIndexName() . '/' . $this->getDomainId() . '.json';
    }

    protected function getDefinitionFileContent(): string
    {
        $definitionFilepath = $this->getDefinitionFilepath();

        if (!is_readable($definitionFilepath)) {
            throw new ElasticsearchCannotReadDefinitionFileException($definitionFilepath);
        }

        return file_get_contents($definitionFilepath);
    }

    protected function getDocumentDefinitionVersion(): string
    {
        return $this->getDefinition()
            |> serialize(...)
            |> md5(...);
    }

    public function getVersionedIndexName(): string
    {
        return sprintf('%s_%s', $this->getIndexAlias(), $this->getDocumentDefinitionVersion());
    }

    public function getIndexAlias(): string
    {
        if ($this->indexPrefix === '') {
            return sprintf('%s_%s', $this->getIndexName(), $this->getDomainId());
        }

        return sprintf('%s_%s_%s', $this->indexPrefix, $this->getIndexName(), $this->getDomainId());
    }

    public function getIndexName(): string
    {
        return $this->indexName;
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }
}
