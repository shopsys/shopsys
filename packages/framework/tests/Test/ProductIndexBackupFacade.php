<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test;

use Elasticsearch\Client;
use Elasticsearch\Namespaces\IndicesNamespace;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchCreateIndexException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;

class ProductIndexBackupFacade
{
    protected bool $isSnapshotCreated = false;

    protected IndicesNamespace $indices;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository $indexRepository
     * @param \Elasticsearch\Client $elasticsearchClient
     */
    public function __construct(
        protected readonly IndexDefinitionLoader $indexDefinitionLoader,
        protected readonly IndexRepository $indexRepository,
        Client $elasticsearchClient,
    ) {
        $this->indices = $elasticsearchClient->indices();
    }

    public function createSnapshot(): void
    {
        if ($this->isSnapshotCreated) {
            return;
        }

        $indexName = $this->getFirstDomainIndexName();
        $backupIndexName = $this->getBackupIndexName();

        $this->cloneIndexToIndex($indexName, $backupIndexName);
        $this->isSnapshotCreated = true;
    }

    public function restoreSnapshotIfPreviouslyCreated(): void
    {
        if (!$this->isSnapshotCreated) {
            return;
        }

        $indexName = $this->getFirstDomainIndexName();
        $backupIndexName = $this->getBackupIndexName();

        $this->cloneIndexToIndex($backupIndexName, $indexName);
        $this->indexRepository->createAlias($this->getFirstDomainIndexDefinition());
        $this->isSnapshotCreated = false;
    }

    /**
     * @param string $sourceIndexName
     * @param string $targetIndexName
     */
    protected function cloneIndexToIndex(string $sourceIndexName, string $targetIndexName): void
    {
        if ($this->indices->exists(['index' => $targetIndexName])) {
            $this->indexRepository->deleteIndex($targetIndexName);
        }

        $this->setWriteBlock($sourceIndexName, true);

        $result = $this->indices->clone([
            'index' => $sourceIndexName,
            'target' => $targetIndexName,
        ]);

        $this->setWriteBlock($sourceIndexName, false);

        if (isset($result['error'])) {
            throw new ElasticsearchCreateIndexException($sourceIndexName, $result['error']);
        }

        $this->setWriteBlock($targetIndexName, false);
    }

    /**
     * @param string $indexName
     * @param bool $blocking
     */
    protected function setWriteBlock(string $indexName, bool $blocking): void
    {
        $this->indices->putSettings([
            'index' => $indexName,
            'body' => [
                'index.blocks.write' => $blocking,
            ],
        ]);
    }

    /**
     * @return string
     */
    protected function getFirstDomainIndexName(): string
    {
        return $this->getFirstDomainIndexDefinition()->getVersionedIndexName();
    }

    /**
     * @return string
     */
    protected function getBackupIndexName(): string
    {
        return $this->getFirstDomainIndexName() . '__backup';
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition
     */
    protected function getFirstDomainIndexDefinition(): IndexDefinition
    {
        return $this->indexDefinitionLoader->getIndexDefinition(
            ProductIndex::getName(),
            1,
        );
    }
}
