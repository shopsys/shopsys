<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchIndexException;

class IndexDefinition
{
    /**
     * @var string
     */
    protected $indexName;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex
     */
    protected $index;

    /**
     * @var string
     */
    protected $definitionsDirectory;

    /**
     * @var string
     */
    protected $indexPrefix;

    /**
     * @var int
     */
    protected $domainId;

    /**
     * @param string $indexName
     * @param string $definitionsDirectory
     * @param string $indexPrefix
     * @param int $domainId
     */
    public function __construct(string $indexName, string $definitionsDirectory, string $indexPrefix, int $domainId)
    {
        $this->indexName = $indexName;
        $this->definitionsDirectory = $definitionsDirectory;
        $this->indexPrefix = $indexPrefix;
        $this->domainId = $domainId;
    }

    /**
     * @return array
     */
    public function getDefinition(): array
    {
        $decodedDefinition = json_decode($this->getDefinitionFileContent(), true);
        if ($decodedDefinition === null) {
            throw ElasticsearchIndexException::invalidJsonInDefinitionFile(
                $this->getIndexName(),
                $this->getDefinitionFilepath()
            );
        }

        return $decodedDefinition;
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
            throw ElasticsearchIndexException::cantReadDefinitionFile($definitionFilepath);
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
