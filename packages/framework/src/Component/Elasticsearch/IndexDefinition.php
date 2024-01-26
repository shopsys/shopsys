<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchCannotReadDefinitionFileException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchInvalidJsonInDefinitionFileException;

class IndexDefinition
{
    protected AbstractIndex $index;

    /**
     * @param string $indexName
     * @param string $definitionsDirectory
     * @param string $indexPrefix
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionModifier $indexDefinitionModifier
     */
    public function __construct(
        protected readonly string $indexName,
        protected readonly string $definitionsDirectory,
        protected readonly string $indexPrefix,
        protected readonly int $domainId,
        protected readonly IndexDefinitionModifier $indexDefinitionModifier,
    ) {
    }

    /**
     * @return array
     */
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

    /**
     * @return string
     */
    protected function getDefinitionFilepath(): string
    {
        return $this->definitionsDirectory . $this->getIndexName() . '/' . $this->getDomainId() . '.json';
    }

    /**
     * @return string
     */
    protected function getDefinitionFileContent(): string
    {
        $definitionFilepath = $this->getDefinitionFilepath();

        if (!is_readable($definitionFilepath)) {
            throw new ElasticsearchCannotReadDefinitionFileException($definitionFilepath);
        }

        return file_get_contents($definitionFilepath);
    }

    /**
     * @return string
     */
    protected function getDocumentDefinitionVersion(): string
    {
        return md5(serialize($this->getDefinition()));
    }

    /**
     * @return string
     */
    public function getVersionedIndexName(): string
    {
        return sprintf('%s_%s', $this->getIndexAlias(), $this->getDocumentDefinitionVersion());
    }

    /**
     * @return string
     */
    public function getIndexAlias(): string
    {
        if ($this->indexPrefix === '') {
            return sprintf('%s_%s', $this->getIndexName(), $this->getDomainId());
        }

        return sprintf('%s_%s_%s', $this->indexPrefix, $this->getIndexName(), $this->getDomainId());
    }

    /**
     * @return string
     */
    public function getIndexName(): string
    {
        return $this->indexName;
    }

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->domainId;
    }
}
