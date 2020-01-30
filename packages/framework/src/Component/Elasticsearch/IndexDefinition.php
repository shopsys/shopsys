<?php

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchIndexException;

class IndexDefinition
{
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
     * DocumentDefinition constructor.
     *
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex $index
     * @param string $definitionsDirectory
     * @param string $indexPrefix
     * @param int $domainId
     */
    public function __construct(AbstractIndex $index, string $definitionsDirectory, string $indexPrefix, int $domainId)
    {
        $this->index = $index;
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
                $this->getIndex()->getName(),
                $this->getDefinitionFilename()
            );
        }

        return $decodedDefinition;
    }

    /**
     * @return string
     */
    protected function getDefinitionFilename(): string
    {
        return $this->getDefinitionsDirectory() . $this->getIndex()->getName() . '/' . $this->getDomainId() . '.json';
    }

    /**
     * @return string
     */
    protected function getDefinitionFileContent(): string
    {
        $definitionFilename = $this->getDefinitionFilename();
        if (!is_readable($definitionFilename)) {
            throw ElasticsearchIndexException::cantReadDefinitionFile($definitionFilename);
        }

        return file_get_contents($definitionFilename);
    }

    /**
     * @return string
     */
    public function getDocumentDefinitionVersion(): string
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
            return sprintf('%s_%s', $this->getIndex()->getName(), $this->getDomainId());
        }
        return sprintf('%s_%s_%s', $this->indexPrefix, $this->getIndex()->getName(), $this->getDomainId());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex
     */
    public function getIndex(): AbstractIndex
    {
        return $this->index;
    }

    /**
     * @return string
     */
    public function getDefinitionsDirectory(): string
    {
        return $this->definitionsDirectory;
    }

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->domainId;
    }
}
