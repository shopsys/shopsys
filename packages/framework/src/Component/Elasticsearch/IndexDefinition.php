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
        protected readonly IndexDefinitionYamlResolver $indexDefinitionYamlResolver,
        protected readonly string $locale = '',
        protected readonly string $environment = '',
    ) {
    }

    public function getDefinition(): array
    {
        $yamlFilepath = $this->getYamlDefinitionFilepath();

        if (is_readable($yamlFilepath)) {
            return $this->getYamlDefinition($yamlFilepath);
        }

        return $this->getJsonDefinition();
    }

    protected function getYamlDefinition(string $yamlFilepath): array
    {
        $yamlContent = file_get_contents($yamlFilepath);

        $decodedDefinition = $this->indexDefinitionYamlResolver->resolveYamlToDefinition(
            $yamlContent,
            $this->domainId,
            $this->locale,
            $this->environment,
        );

        return $this->indexDefinitionModifier->modifyDefinition($decodedDefinition);
    }

    protected function getJsonDefinition(): array
    {
        $decodedDefinition = json_decode($this->getJsonDefinitionFileContent(), true);

        if ($decodedDefinition === null) {
            throw new ElasticsearchInvalidJsonInDefinitionFileException(
                $this->getIndexName(),
                $this->getJsonDefinitionFilepath(),
            );
        }

        return $this->indexDefinitionModifier->modifyDefinition($decodedDefinition);
    }

    protected function getYamlDefinitionFilepath(): string
    {
        return $this->definitionsDirectory . $this->getIndexName() . '.yaml';
    }

    /**
     * @deprecated Use YAML definition files instead. JSON definitions will be removed in a future version.
     */
    protected function getDefinitionFilepath(): string
    {
        return $this->getJsonDefinitionFilepath();
    }

    protected function getJsonDefinitionFilepath(): string
    {
        return $this->definitionsDirectory . $this->getIndexName() . '/' . $this->getDomainId() . '.json';
    }

    /**
     * @deprecated Use YAML definition files instead. JSON definitions will be removed in a future version.
     */
    protected function getDefinitionFileContent(): string
    {
        return $this->getJsonDefinitionFileContent();
    }

    protected function getJsonDefinitionFileContent(): string
    {
        $definitionFilepath = $this->getJsonDefinitionFilepath();

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
